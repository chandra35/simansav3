"""SIMANSA Face Python Edge Agent.

Multi-face recognition experiment. Camera frames never leave this computer and
attendance is deliberately not recorded. Press Q/Esc to exit, F for fullscreen,
or R to rebuild the local reference embeddings.
"""

from __future__ import annotations

import json
import math
import queue
import re
import sys
import threading
import time
from dataclasses import dataclass, field
from pathlib import Path
from typing import Any

import cv2
import numpy as np
import pyttsx3
import requests
from insightface.app import FaceAnalysis


VERSION = "0.1.0"
ROOT = Path(__file__).resolve().parent
CONFIG_PATH = ROOT / "config.json"
CACHE_PATH = ROOT / "face_embeddings.npz"
CACHE_META_PATH = ROOT / "face_embeddings.meta.json"


def load_config() -> dict[str, Any]:
    if not CONFIG_PATH.exists():
        raise RuntimeError("config.json belum ada. Salin config.example.json lalu isi token perangkat.")
    config = json.loads(CONFIG_PATH.read_text(encoding="utf-8"))
    required = ["api_base_url", "device_token", "device_name"]
    missing = [key for key in required if not str(config.get(key, "")).strip()]
    if missing:
        raise RuntimeError("Konfigurasi wajib belum diisi: " + ", ".join(missing))
    if len(str(config["device_token"])) < 32:
        raise RuntimeError("Token perangkat tidak valid.")
    return config


class SimansaApi:
    def __init__(self, config: dict[str, Any]) -> None:
        self.base_url = str(config["api_base_url"]).rstrip("/")
        self.verify_ssl = bool(config.get("verify_ssl", True))
        self.session = requests.Session()
        self.session.headers.update({
            "Authorization": f"Bearer {config['device_token']}",
            "Accept": "application/json",
            "User-Agent": f"SIMANSA-Face-Python/{VERSION}",
        })

    def bootstrap(self) -> dict[str, Any]:
        response = self.session.get(f"{self.base_url}/bootstrap", timeout=30, verify=self.verify_ssl)
        response.raise_for_status()
        # Tolerate a legacy UTF-8 BOM while older SIMANSA deployments are upgraded.
        return json.loads(response.content.decode("utf-8-sig"))

    def heartbeat(self, payload: dict[str, Any]) -> None:
        try:
            response = self.session.post(
                f"{self.base_url}/heartbeat", json=payload, timeout=6, verify=self.verify_ssl
            )
            response.raise_for_status()
        except requests.RequestException as error:
            print(f"[heartbeat] {error}")

    def image(self, url: str) -> np.ndarray | None:
        try:
            # Reference assets are public storage URLs. Never forward the device
            # Bearer token to a storage/CDN host.
            response = requests.get(
                url,
                headers={"User-Agent": f"SIMANSA-Face-Python/{VERSION}"},
                timeout=20,
                verify=self.verify_ssl,
            )
            response.raise_for_status()
            data = np.frombuffer(response.content, dtype=np.uint8)
            return cv2.imdecode(data, cv2.IMREAD_COLOR)
        except (requests.RequestException, ValueError) as error:
            print(f"[reference] gagal mengambil foto: {error}")
            return None


class VoiceWorker:
    def __init__(self, enabled: bool) -> None:
        self.enabled = enabled
        self.items: queue.Queue[str | None] = queue.Queue(maxsize=20)
        self.thread = threading.Thread(target=self._run, daemon=True)
        self.thread.start()

    def say(self, name: str) -> None:
        if not self.enabled:
            return
        text = f"Selamat datang, {speech_name(name)}"
        try:
            self.items.put_nowait(text)
        except queue.Full:
            pass

    def close(self) -> None:
        try:
            self.items.put_nowait(None)
        except queue.Full:
            pass

    def _run(self) -> None:
        try:
            engine = pyttsx3.init()
            engine.setProperty("rate", 165)
            while True:
                text = self.items.get()
                if text is None:
                    break
                engine.say(text)
                engine.runAndWait()
        except Exception as error:  # Voice must never stop recognition.
            print(f"[voice] dinonaktifkan: {error}")


def speech_name(name: str) -> str:
    letters = {"A": "a", "B": "be", "C": "ce", "D": "de", "E": "e", "F": "ef", "G": "ge", "H": "ha", "I": "i", "J": "je", "K": "ka", "L": "el", "M": "em", "N": "en", "O": "o", "P": "pe", "Q": "ki", "R": "er", "S": "es", "T": "te", "U": "u", "V": "ve", "W": "we", "X": "eks", "Y": "ye", "Z": "zet"}

    def spell(match: re.Match[str]) -> str:
        return " ".join(letters.get(char.upper(), char) for char in re.findall(r"[A-Za-z]", match.group(0)))

    return re.sub(r"\b[A-Za-z](?:\.[A-Za-z]{1,3}){1,4}\b", spell, name.replace(",", " ")).strip()


@dataclass
class Profile:
    user_id: str
    name: str
    user_type: str
    embedding: np.ndarray


@dataclass
class Detection:
    bbox: np.ndarray
    center: tuple[int, int]
    name: str = "Unknown"
    user_id: str | None = None
    user_type: str | None = None
    score: float = 0.0
    track_id: int | None = None


@dataclass
class Track:
    track_id: int
    center: tuple[int, int]
    user_id: str | None = None
    streak: int = 0
    missed: int = 0
    last_seen: float = field(default_factory=time.time)


class Tracker:
    # ponytail: centroid matching is enough for the single-door trial; replace
    # with ByteTrack when dense, crossing crowds become a real requirement.
    def __init__(self, max_distance: float = 130.0) -> None:
        self.max_distance = max_distance
        self.next_id = 1
        self.tracks: dict[int, Track] = {}

    def update(self, detections: list[Detection]) -> None:
        available = set(self.tracks)
        for detection in detections:
            best_id, best_distance = None, self.max_distance
            for track_id in available:
                distance = math.dist(detection.center, self.tracks[track_id].center)
                if distance < best_distance:
                    best_id, best_distance = track_id, distance
            if best_id is None:
                best_id = self.next_id
                self.next_id += 1
                self.tracks[best_id] = Track(best_id, detection.center)
            else:
                available.remove(best_id)

            track = self.tracks[best_id]
            track.center = detection.center
            track.last_seen = time.time()
            track.missed = 0
            track.streak = track.streak + 1 if detection.user_id and track.user_id == detection.user_id else (1 if detection.user_id else 0)
            track.user_id = detection.user_id
            detection.track_id = best_id

        for track_id in available:
            self.tracks[track_id].missed += 1
        self.tracks = {key: value for key, value in self.tracks.items() if value.missed <= 12}


def normalize(embedding: np.ndarray) -> np.ndarray:
    norm = np.linalg.norm(embedding)
    return embedding.astype(np.float32) / max(float(norm), 1e-12)


def build_profiles(api: SimansaApi, model: FaceAnalysis, bootstrap: dict[str, Any], force: bool = False) -> list[Profile]:
    revision = str(bootstrap.get("revision", ""))
    if not force and CACHE_PATH.exists() and CACHE_META_PATH.exists():
        meta = json.loads(CACHE_META_PATH.read_text(encoding="utf-8"))
        if meta.get("revision") == revision:
            cached = np.load(CACHE_PATH, allow_pickle=False)
            return [Profile(str(uid), str(name), str(kind), embedding) for uid, name, kind, embedding in zip(cached["user_ids"], cached["names"], cached["types"], cached["embeddings"])]

    profiles: list[Profile] = []
    people = bootstrap.get("people", [])
    print(f"[sync] membentuk embedding {len(people)} profil...")
    for index, person in enumerate(people, start=1):
        embeddings: list[np.ndarray] = []
        for url in person.get("photo_urls", []):
            image = api.image(str(url))
            if image is None:
                continue
            faces = model.get(image)
            if faces:
                face = max(faces, key=lambda item: float((item.bbox[2] - item.bbox[0]) * (item.bbox[3] - item.bbox[1])))
                embeddings.append(normalize(face.embedding))
        if embeddings:
            profiles.append(Profile(str(person["user_id"]), str(person["name"]), str(person["user_type"]), normalize(np.mean(embeddings, axis=0))))
        print(f"\r[sync] {index}/{len(people)} · siap {len(profiles)}", end="", flush=True)
    print()

    if profiles:
        np.savez_compressed(CACHE_PATH, user_ids=np.array([p.user_id for p in profiles]), names=np.array([p.name for p in profiles]), types=np.array([p.user_type for p in profiles]), embeddings=np.stack([p.embedding for p in profiles]))
        CACHE_META_PATH.write_text(json.dumps({"revision": revision, "created_at": time.time()}), encoding="utf-8")
    return profiles


def recognize(frame: np.ndarray, model: FaceAnalysis, profiles: list[Profile], profile_matrix: np.ndarray | None, threshold: float) -> list[Detection]:
    faces = model.get(frame)
    if profile_matrix is None:
        return [Detection(face.bbox, (int((face.bbox[0] + face.bbox[2]) / 2), int((face.bbox[1] + face.bbox[3]) / 2))) for face in faces]
    results: list[Detection] = []
    for face in faces:
        embedding = normalize(face.embedding)
        scores = profile_matrix @ embedding
        best = int(np.argmax(scores))
        score = float(scores[best])
        profile = profiles[best]
        center = (int((face.bbox[0] + face.bbox[2]) / 2), int((face.bbox[1] + face.bbox[3]) / 2))
        results.append(Detection(face.bbox, center, profile.name if score >= threshold else "Unknown", profile.user_id if score >= threshold else None, profile.user_type if score >= threshold else None, score))
    return results


def draw(frame: np.ndarray, detections: list[Detection], fps: float, profiles: int, state: str) -> None:
    for item in detections:
        x1, y1, x2, y2 = item.bbox.astype(int)
        known = item.user_id is not None
        color = (40, 200, 110) if known else (40, 170, 245)
        cv2.rectangle(frame, (x1, y1), (x2, y2), color, 2)
        label = f"#{item.track_id or '-'} {item.name} {item.score:.2f}" if known else f"#{item.track_id or '-'} Unknown"
        cv2.rectangle(frame, (x1, max(0, y1 - 28)), (min(frame.shape[1], x1 + max(180, len(label) * 8)), y1), color, -1)
        cv2.putText(frame, label, (x1 + 5, y1 - 8), cv2.FONT_HERSHEY_SIMPLEX, 0.52, (10, 20, 35), 2, cv2.LINE_AA)
    cv2.rectangle(frame, (0, 0), (frame.shape[1], 42), (7, 17, 37), -1)
    cv2.putText(frame, f"SIMANSA FACE PYTHON · SIMULASI | {state} | {fps:.1f} FPS | {len(detections)} wajah | {profiles} profil", (14, 27), cv2.FONT_HERSHEY_SIMPLEX, 0.58, (240, 248, 255), 2, cv2.LINE_AA)


def main() -> None:
    config = load_config()
    api = SimansaApi(config)
    api.heartbeat({"device_name": config["device_name"], "agent_version": VERSION, "state": "starting", "message": "Memuat model InsightFace"})
    detector_size = int(config.get("detector_size", 640))
    model = FaceAnalysis(name="buffalo_l", providers=["CPUExecutionProvider"])
    model.prepare(ctx_id=0, det_size=(detector_size, detector_size))
    bootstrap = api.bootstrap()
    profiles = build_profiles(api, model, bootstrap)
    profile_matrix = np.stack([profile.embedding for profile in profiles]) if profiles else None
    server_settings = bootstrap.get("settings", {})
    threshold = float(config.get("match_threshold", server_settings.get("match_threshold", 0.42)))
    confirmations = int(config.get("confirm_frames", server_settings.get("confirm_frames", 3)))
    cooldown = float(config.get("cooldown_seconds", server_settings.get("cooldown_seconds", 20)))
    process_every = max(1, int(config.get("process_every_n_frames", 2)))

    camera = cv2.VideoCapture(int(config.get("camera_index", 0)), cv2.CAP_DSHOW if sys.platform == "win32" else cv2.CAP_ANY)
    camera.set(cv2.CAP_PROP_FRAME_WIDTH, int(config.get("camera_width", 1280)))
    camera.set(cv2.CAP_PROP_FRAME_HEIGHT, int(config.get("camera_height", 720)))
    if not camera.isOpened():
        raise RuntimeError("Kamera tidak dapat dibuka. Periksa camera_index dan izin Windows.")

    voice = VoiceWorker(bool(config.get("voice_enabled", True)))
    tracker = Tracker()
    greeted_at: dict[str, float] = {}
    window = "SIMANSA Face Python - SIMULASI"
    cv2.namedWindow(window, cv2.WINDOW_NORMAL)
    if bool(config.get("show_fullscreen", False)):
        cv2.setWindowProperty(window, cv2.WND_PROP_FULLSCREEN, cv2.WINDOW_FULLSCREEN)

    frame_number, frame_counter = 0, 0
    fps, fps_started = 0.0, time.perf_counter()
    detections: list[Detection] = []
    last_heartbeat, last_name, last_confidence = 0.0, None, None
    try:
        while True:
            ok, frame = camera.read()
            if not ok:
                time.sleep(0.1)
                continue
            frame_number += 1
            frame_counter += 1
            if frame_number % process_every == 0:
                detections = recognize(frame, model, profiles, profile_matrix, threshold)
                tracker.update(detections)
                now_value = time.time()
                for item in detections:
                    track = tracker.tracks.get(item.track_id or -1)
                    if item.user_id and track and track.streak >= confirmations and now_value - greeted_at.get(item.user_id, 0) >= cooldown:
                        greeted_at[item.user_id] = now_value
                        last_name, last_confidence = item.name, item.score
                        voice.say(item.name)

            elapsed = time.perf_counter() - fps_started
            if elapsed >= 1:
                fps, frame_counter, fps_started = frame_counter / elapsed, 0, time.perf_counter()
            draw(frame, detections, fps, len(profiles), "ONLINE")
            cv2.imshow(window, frame)

            if time.time() - last_heartbeat >= float(server_settings.get("heartbeat_seconds", 10)):
                api.heartbeat({"device_name": config["device_name"], "agent_version": VERSION, "state": "running", "fps": round(fps, 1), "faces_in_frame": len(detections), "profiles": len(profiles), "recognized_name": last_name, "confidence": last_confidence, "message": "Mode simulasi; presensi tidak dicatat"})
                last_heartbeat = time.time()

            key = cv2.waitKey(1) & 0xFF
            if key in (27, ord("q")):
                break
            if key == ord("f"):
                current = cv2.getWindowProperty(window, cv2.WND_PROP_FULLSCREEN)
                cv2.setWindowProperty(window, cv2.WND_PROP_FULLSCREEN, cv2.WINDOW_NORMAL if current == cv2.WINDOW_FULLSCREEN else cv2.WINDOW_FULLSCREEN)
            if key == ord("r"):
                api.heartbeat({"device_name": config["device_name"], "agent_version": VERSION, "state": "syncing", "message": "Sinkron ulang referensi"})
                bootstrap = api.bootstrap()
                profiles = build_profiles(api, model, bootstrap, force=True)
                profile_matrix = np.stack([profile.embedding for profile in profiles]) if profiles else None
    finally:
        api.heartbeat({"device_name": config["device_name"], "agent_version": VERSION, "state": "stopped", "fps": round(fps, 1), "faces_in_frame": 0, "profiles": len(profiles), "message": "Agent dihentikan"})
        voice.close()
        camera.release()
        cv2.destroyAllWindows()


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        pass
    except Exception as error:
        print(f"\nERROR: {error}")
        input("Tekan Enter untuk menutup...")
        raise

<?php

namespace App\Services;

use App\Models\SiteSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected $pageId;
    protected $accessToken;
    protected $baseUrl = 'https://graph.facebook.com/v18.0';

    public function __construct()
    {
        $settings = SiteSettings::instance();
        $this->pageId = $settings->facebook_page_id;
        $this->accessToken = $settings->facebook_access_token;
    }

    /**
     * Check if Facebook is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->pageId) && !empty($this->accessToken);
    }

    /**
     * Check connection to Facebook.
     */
    public function checkConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Facebook belum dikonfigurasi. Silakan isi Page ID dan Access Token.',
            ];
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->pageId}", [
                'access_token' => $this->accessToken,
                'fields' => 'name,id',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil!',
                    'page_name' => $data['name'] ?? 'Unknown',
                    'page_id' => $data['id'] ?? null,
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Gagal terhubung ke Facebook.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Facebook connection error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Post to Facebook page.
     */
    public function postToPage(string $message, ?string $link = null, ?string $imageUrl = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Facebook belum dikonfigurasi.',
            ];
        }

        try {
            $data = [
                'access_token' => $this->accessToken,
                'message' => $message,
            ];

            if ($link) {
                $data['link'] = $link;
            }

            // If there's an image, post as photo
            if ($imageUrl) {
                $response = Http::post("{$this->baseUrl}/{$this->pageId}/photos", array_merge($data, [
                    'url' => $imageUrl,
                ]));
            } else {
                $response = Http::post("{$this->baseUrl}/{$this->pageId}/feed", $data);
            }

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'message' => 'Berhasil diposting ke Facebook!',
                    'post_id' => $result['id'] ?? $result['post_id'] ?? null,
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Gagal posting ke Facebook.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Facebook post error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a post from Facebook.
     */
    public function deletePost(string $postId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Facebook belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::delete("{$this->baseUrl}/{$postId}", [
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Post berhasil dihapus dari Facebook.',
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Gagal menghapus post.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Facebook delete error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get page posts.
     */
    public function getPagePosts(int $limit = 10): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Facebook belum dikonfigurasi.',
                'posts' => [],
            ];
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->pageId}/posts", [
                'access_token' => $this->accessToken,
                'fields' => 'id,message,created_time,full_picture',
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'posts' => $data['data'] ?? [],
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Gagal mengambil posts.',
                    'posts' => [],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Facebook get posts error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'posts' => [],
            ];
        }
    }
}

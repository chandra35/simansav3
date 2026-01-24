@extends('adminlte::page')

@section('title', 'Akses Ditolak')

@section('content_header')
    <h1 class="text-danger"><i class="fas fa-ban"></i> Akses Ditolak</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-danger card-outline">
            <div class="card-header text-center">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Error 403 - Forbidden</h3>
            </div>
            <div class="card-body text-center py-5">
                <!-- Animated SVG Illustration -->
                <div class="illustration-container mb-4">
                    <svg class="animated-lock" width="150" height="150" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <!-- Lock Body -->
                        <rect x="60" y="90" width="80" height="70" rx="10" fill="#dc3545" class="lock-body">
                            <animate attributeName="y" values="90;85;90" dur="2s" repeatCount="indefinite"/>
                        </rect>
                        
                        <!-- Lock Shackle -->
                        <path d="M 75 90 Q 75 50, 100 50 Q 125 50, 125 90" stroke="#dc3545" stroke-width="8" fill="none" class="lock-shackle">
                            <animate attributeName="stroke-width" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                        </path>
                        
                        <!-- Keyhole -->
                        <circle cx="100" cy="115" r="8" fill="#fff" opacity="0.9"/>
                        <rect x="96" y="115" width="8" height="20" fill="#fff" opacity="0.9"/>
                        
                        <!-- Warning Symbol -->
                        <g class="warning-symbol" opacity="0">
                            <circle cx="170" cy="40" r="15" fill="#ffc107"/>
                            <text x="170" y="48" font-size="20" fill="#fff" text-anchor="middle" font-weight="bold">!</text>
                            <animate attributeName="opacity" values="0;1;0" dur="1.5s" repeatCount="indefinite"/>
                        </g>
                        
                        <!-- Cross marks -->
                        <g class="cross-marks">
                            <line x1="40" y1="40" x2="50" y2="50" stroke="#dc3545" stroke-width="3" opacity="0.5">
                                <animate attributeName="opacity" values="0.5;1;0.5" dur="1s" repeatCount="indefinite"/>
                            </line>
                            <line x1="50" y1="40" x2="40" y2="50" stroke="#dc3545" stroke-width="3" opacity="0.5">
                                <animate attributeName="opacity" values="0.5;1;0.5" dur="1s" repeatCount="indefinite"/>
                            </line>
                            
                            <line x1="150" y1="170" x2="160" y2="180" stroke="#dc3545" stroke-width="3" opacity="0.5">
                                <animate attributeName="opacity" values="0.5;1;0.5" dur="1.2s" repeatCount="indefinite"/>
                            </line>
                            <line x1="160" y1="170" x2="150" y2="180" stroke="#dc3545" stroke-width="3" opacity="0.5">
                                <animate attributeName="opacity" values="0.5;1;0.5" dur="1.2s" repeatCount="indefinite"/>
                            </line>
                        </g>
                    </svg>
                </div>

                <!-- Main Message -->
                <h2 class="mb-3 text-danger font-weight-bold">Akses Ditolak!</h2>
                <p class="text-muted mb-4 lead">
                    <i class="fas fa-user-slash"></i> Anda tidak memiliki izin untuk mengakses halaman ini.
                </p>

                <!-- Error Message Box -->
                <div class="alert alert-danger mx-auto" style="max-width: 600px;">
                    <i class="fas fa-info-circle"></i> <strong>Pesan Error:</strong><br>
                    <code class="text-dark">{{ $exception->getMessage() ?: 'Unauthorized action.' }}</code>
                </div>

                <!-- Info Box -->
                <div class="info-box bg-light mx-auto mb-4" style="max-width: 600px;">
                    <span class="info-box-icon bg-danger"><i class="fas fa-shield-alt"></i></span>
                    <div class="info-box-content text-left">
                        <span class="info-box-text">Kemungkinan Penyebab:</span>
                        <span class="info-box-number" style="font-size: 14px;">
                            • Anda tidak memiliki permission yang diperlukan<br>
                            • Session telah berubah atau expired<br>
                            • Hubungi administrator untuk bantuan
                        </span>
                    </div>
                </div>

                <!-- Countdown -->
                <div class="countdown-box mb-4">
                    <p class="mb-2">
                        <i class="fas fa-clock"></i> Dialihkan ke dashboard dalam 
                        <span id="countdown" class="badge badge-warning badge-lg" style="font-size: 1.2em;">5</span> 
                        detik
                    </p>
                    <div class="progress" style="height: 5px; max-width: 400px; margin: 0 auto;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div>
                    <a href="{{ getDashboardRoute() }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-home"></i> Kembali ke Dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Halaman Sebelumnya
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Illustration Container */
    .illustration-container {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-15px);
        }
    }

    /* Animated Lock SVG */
    .animated-lock {
        filter: drop-shadow(0 5px 15px rgba(220, 53, 69, 0.3));
    }

    .lock-body {
        transform-origin: center;
    }

    /* Fade in animation */
    .card {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Pulse animation for countdown */
    #countdown {
        animation: pulse 1s infinite;
        display: inline-block;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.15);
        }
    }

    /* Custom styles */
    .badge-lg {
        padding: 8px 15px;
        font-weight: bold;
    }

    code {
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 13px;
    }

    /* Button hover effect */
    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Info box animation */
    .info-box {
        animation: slideInRight 0.5s ease-out 0.3s both;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Alert animation */
    .alert {
        animation: slideInLeft 0.5s ease-out 0.2s both;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Progress bar smooth transition */
    #progressBar {
        transition: width 1s linear;
    }

    /* Countdown box shake on last second */
    .countdown-shake {
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        let timeLeft = 5;
        const countdownElement = $('#countdown');
        const progressBar = $('#progressBar');
        const countdownBox = $('.countdown-box');
        
        // Countdown timer
        const countdownInterval = setInterval(function() {
            timeLeft--;
            countdownElement.text(timeLeft);
            
            // Update progress bar
            const progress = (timeLeft / 5) * 100;
            progressBar.css('width', progress + '%');
            
            // Add shake animation on last 2 seconds
            if (timeLeft <= 2) {
                countdownBox.addClass('countdown-shake');
                setTimeout(() => countdownBox.removeClass('countdown-shake'), 500);
            }
            
            // Change badge color
            if (timeLeft <= 2) {
                countdownElement.removeClass('badge-warning').addClass('badge-danger');
            }
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                
                // Show loading before redirect
                Swal.fire({
                    title: 'Mengalihkan...',
                    html: '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 500,
                    didOpen: () => {
                        setTimeout(() => {
                            window.location.href = '{{ getDashboardRoute() }}';
                        }, 500);
                    }
                });
            }
        }, 1000);
        
        // Clear countdown if user clicks a button
        $('a.btn').on('click', function(e) {
            if (!$(this).attr('href').includes('javascript:')) {
                clearInterval(countdownInterval);
                
                // Show loading animation
                Swal.fire({
                    title: 'Memuat...',
                    html: '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            }
        });
        
        // Pause countdown on hover
        countdownBox.hover(
            function() {
                clearInterval(countdownInterval);
                $(this).css('opacity', '0.7');
            },
            function() {
                $(this).css('opacity', '1');
                // Note: Countdown won't resume, just paused
            }
        );
    });
</script>
@stop

<?php

declare(strict_types=1);
session_start();

/**
 * Escape helper
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Basic config (EDIT THESE)
 */
$biz = [
    'name'     => 'Dynamic Computer System',
    'tagline'  => 'Computers • Custom PC Builds • CCTV • Networking',
    'phone'    => '+9471 820 9511',
    'whatsapp' => '+94XXXXXXXXX',
    'address'  => 'No. 268/A Anagarika Dharmapala Mawatha, Matara',
    'hours'    => [
        'Mon - Sat: 9:00 AM - 6:00 PM',
        'Sunday: Closed'
    ],
    // Optional: if mail() is configured on your server
    'lead_to_email' => 'youremail@example.com',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$success = false;
$errors  = [];
$old = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'service' => '',
    'message' => ''
];

$services = [
    'Custom PC Build',
    'PC Repair & Upgrade',
    'Laptop Purchase',
    'CCTV Sales',
    'CCTV Installation',
    'Network Setup',
    'General Inquiry'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errors[] = 'Security check failed. Please refresh and try again.';
    }

    $old['name']    = trim((string)($_POST['name'] ?? ''));
    $old['phone']   = trim((string)($_POST['phone'] ?? ''));
    $old['email']   = trim((string)($_POST['email'] ?? ''));
    $old['service'] = trim((string)($_POST['service'] ?? ''));
    $old['message'] = trim((string)($_POST['message'] ?? ''));

    if ($old['name'] === '')   $errors[] = 'Name is required.';
    if ($old['phone'] === '')  $errors[] = 'Phone is required.';
    if ($old['message'] === '') $errors[] = 'Message is required.';

    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is not valid.';
    }

    // If no errors: save lead + optional email
    if (!$errors) {
        // 1) Save to CSV (recommended — always works)
        $leadLine = [
            date('Y-m-d H:i:s'),
            $old['name'],
            $old['phone'],
            $old['email'],
            $old['service'],
            preg_replace("/\r|\n/", " ", $old['message'])
        ];

        $leadsDir = __DIR__ . '/data';
        if (!is_dir($leadsDir)) @mkdir($leadsDir, 0755, true);
        $csvFile = $leadsDir . '/leads.csv';
        $fp = @fopen($csvFile, 'a');
        if ($fp) {
            fputcsv($fp, $leadLine);
            fclose($fp);
        }

        // 2) Optional: send email (only works if your server supports mail())
        if (!empty($biz['lead_to_email'])) {
            $to = $biz['lead_to_email'];
            $subject = $biz['name'] . " - New Website Inquiry";
            $body =
                "New Inquiry\n\n" .
                "Name: {$old['name']}\n" .
                "Phone: {$old['phone']}\n" .
                "Email: {$old['email']}\n" .
                "Service: {$old['service']}\n\n" .
                "Message:\n{$old['message']}\n";

            $headers = "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
            @mail($to, $subject, $body, $headers);
        }

        $success = true;

        // Reset old fields after success
        $old = ['name' => '', 'phone' => '', 'email' => '', 'service' => '', 'message' => ''];

        // Rotate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
}

// Helpers for links
$telLink = 'tel:' . preg_replace('/\s+/', '', $biz['phone']);
$waNumber = preg_replace('/\D+/', '', $biz['whatsapp']); // digits only
$waLink = 'https://wa.me/' . $waNumber;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($biz['name']) ?> | Computers, Custom PC Builds & CCTV in Matara</title>
    <meta name="description" content="Custom PC builds, laptops, genuine computer parts, repairs, networking, and professional CCTV installation in Matara, Sri Lanka. CCTV partners: Hikvision & Uniarch.">

    <!-- Tailwind CDN (fast setup). For production, compile Tailwind for best performance. -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="min-h-screen bg-white text-gray-900">
    <!-- NAVBAR -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="#home" class="flex items-center space-x-2">
                    <div class="w-10 h-10 flex items-center justify-center">
                        <img src="./assets/logo.png" alt="Dynamic Computer System Logo" class="w-10 h-10 object-contain">
                    </div>
                    <div class="leading-tight">
                        <div class="font-extrabold text-base sm:text-lg"><?= e($biz['name']) ?></div>
                        <div class="text-xs text-gray-500 hidden sm:block"><?= e($biz['tagline']) ?></div>
                    </div>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 transition">Home</a>
                    <a href="#products" class="text-gray-700 hover:text-blue-600 transition">Products</a>
                    <a href="#services" class="text-gray-700 hover:text-blue-600 transition">Services</a>
                    <a href="#pc-builds" class="text-gray-700 hover:text-blue-600 transition">PC Builds</a>
                    <a href="#cctv" class="text-gray-700 hover:text-blue-600 transition">CCTV</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-600 transition">About</a>
                    <a href="#contact" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition shadow-sm">
                        Get a Quote
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition" aria-label="Open menu">
                    <i id="menuIcon" data-lucide="menu" class="w-6 h-6"></i>
                    <i id="closeIcon" data-lucide="x" class="w-6 h-6 hidden"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-gray-100 bg-white">
            <div class="px-4 py-3 space-y-2">
                <a class="block py-2 text-gray-700 hover:text-blue-600" href="#home">Home</a>
                <a class="block py-2 text-gray-700 hover:text-blue-600" href="#products">Products</a>
                <a class="block py-2 text-gray-700 hover:text-blue-600" href="#services">Services</a>
                <a class="block py-2 text-gray-700 hover:text-blue-600" href="#pc-builds">PC Builds</a>
                <a class="block py-2 text-gray-700 hover:text-blue-600" href="#cctv">CCTV</a>
                <a class="block py-2 text-gray-700 hover:text-blue-600" href="#about">About</a>
                <a class="block py-2 bg-blue-600 text-white rounded-xl text-center hover:bg-blue-700 transition" href="#contact">Get a Quote</a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section id="home" class="relative overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#06162F] via-[#0A2342] to-[#0F2F5F]"></div>

        <!-- Decorative blobs -->
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-white/10 rounded-full blur-2xl"></div>

        <!-- Background Image overlay -->
        <div class="absolute inset-0 opacity-15"
            style="background-image:url('assets/images/hero/hero-tech.jpg'); background-size:cover; background-position:center;">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white px-4 py-2 rounded-full mb-6">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        <span class="text-sm font-semibold">Genuine Parts • Clean Builds • Pro Installation</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                        <?= e($biz['name']) ?>
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-blue-100 leading-relaxed">
                        Your trusted partner for <b>custom PC builds</b>, <b>laptops</b>, <b>genuine computer parts</b>,
                        and <b>professional CCTV installation</b> in <b>Matara, Sri Lanka</b>.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        <a href="<?= e($telLink) ?>" class="bg-white text-blue-700 px-7 py-4 rounded-xl hover:bg-blue-50 transition flex items-center justify-center gap-2 font-semibold shadow-lg">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                            Call Now
                        </a>
                        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="bg-green-500 text-white px-7 py-4 rounded-xl hover:bg-green-600 transition flex items-center justify-center gap-2 font-semibold shadow-lg">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                            WhatsApp
                        </a>
                    </div>

                    <div class="mt-10 grid grid-cols-3 gap-3 sm:gap-4">
                        <div class="bg-white/10 border border-white/20 rounded-xl p-4 hover:bg-white/15 transition">
                            <i data-lucide="check-circle" class="w-7 h-7 text-green-300"></i>
                            <div class="mt-2 text-white font-semibold text-sm">Genuine Parts</div>
                            <div class="text-blue-100 text-xs mt-1">Trusted brands</div>
                        </div>
                        <div class="bg-white/10 border border-white/20 rounded-xl p-4 hover:bg-white/15 transition">
                            <i data-lucide="cpu" class="w-7 h-7 text-yellow-300"></i>
                            <div class="mt-2 text-white font-semibold text-sm">Custom Builds</div>
                            <div class="text-blue-100 text-xs mt-1">Budget to high-end</div>
                        </div>
                        <div class="bg-white/10 border border-white/20 rounded-xl p-4 hover:bg-white/15 transition">
                            <i data-lucide="video" class="w-7 h-7 text-red-300"></i>
                            <div class="mt-2 text-white font-semibold text-sm">CCTV Pro</div>
                            <div class="text-blue-100 text-xs mt-1">Home & business</div>
                        </div>
                    </div>

                    
                </div>

                <!-- Hero image card -->
                <div class="relative">
                    <div class="absolute inset-0 bg-white/10 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-gray-900/60 border border-white/15 rounded-3xl overflow-hidden shadow-2xl">
                        <img
                            src="./assets/images/hero/first-first-pc-1.jpg"
                            alt="Custom PC Build"
                            class="w-full h-[360px] sm:h-[420px] object-cover"
                            loading="lazy" />
                        <div class="p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-white font-bold text-lg">Build • Upgrade • Secure</div>
                                    <div class="text-blue-100 text-sm mt-1">Fast service • Clean wiring • After-sales support</div>
                                </div>
                                <div class="bg-blue-600 text-white px-3 py-2 rounded-xl font-semibold text-sm shadow">
                                    Matara
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div class="bg-white/10 rounded-xl p-3 text-center">
                                    <div class="text-white font-bold">PC</div>
                                    <div class="text-blue-100 text-xs mt-1">Builds</div>
                                </div>
                                <div class="bg-white/10 rounded-xl p-3 text-center">
                                    <div class="text-white font-bold">CCTV</div>
                                    <div class="text-blue-100 text-xs mt-1">Install</div>
                                </div>
                                <div class="bg-white/10 rounded-xl p-3 text-center">
                                    <div class="text-white font-bold">WiFi</div>
                                    <div class="text-blue-100 text-xs mt-1">Setup</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- PRODUCTS -->
    <section id="products" class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-extrabold">Our Products & Categories</h2>
                <p class="text-xl text-gray-600 mt-3 max-w-2xl mx-auto">
                    Everything you need for your computing and security needs.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card -->
                <div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-blue-200 hover:shadow-2xl transition">
                    <img src="https://media.istockphoto.com/id/619052288/photo/laptop-and-computer-parts.jpg?s=612x612&w=0&k=20&c=ejIT6Owx79tk4E3z4FxS16kWQHPHL3VDE7TQRMauMLU=" alt="PC Parts" class="w-full h-44 object-cover" loading="lazy">
                    <div class="p-7">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                                <i data-lucide="monitor" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold">PC Parts</h3>
                        </div>
                        <ul class="mt-4 text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-blue-600 rounded-full"></span>Processors & Motherboards</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-blue-600 rounded-full"></span>RAM & Storage Drives</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-blue-600 rounded-full"></span>Graphics Cards & PSUs</li>
                        </ul>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-blue-600 font-semibold group-hover:translate-x-1 transition">
                            Ask for availability <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-green-200 hover:shadow-2xl transition">
                    <img src="https://media.currys.biz/i/currysprod/tech-talk-gaming-pc-accessories-hero-image?fmt=auto" alt="Gaming Accessories" class="w-full h-44 object-cover" loading="lazy">
                    <div class="p-7">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
                                <i data-lucide="mouse" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold">Gaming Accessories</h3>
                        </div>
                        <ul class="mt-4 text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-green-600 rounded-full"></span>Gaming Keyboards & Mice</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-green-600 rounded-full"></span>Headsets & Controllers</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-green-600 rounded-full"></span>Monitors & RGB Lighting</li>
                        </ul>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-green-600 font-semibold group-hover:translate-x-1 transition">
                            Get recommendations <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-orange-200 hover:shadow-2xl transition">
                    <img src="https://article.images.consumerreports.org/image/upload/w_652,f_auto,q_auto,ar_16:9,c_lfill/v1751564368/prod/content/dam/CRO-Images-2025/Electronics/CR-Tech-Inlinehero-best-laptops-0725" alt="Laptops" class="w-full h-44 object-cover" loading="lazy">
                    <div class="p-7">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center">
                                <i data-lucide="laptop" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold">Laptops</h3>
                        </div>
                        <ul class="mt-4 text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-orange-600 rounded-full"></span>Business Laptops</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-orange-600 rounded-full"></span>Gaming Laptops</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-orange-600 rounded-full"></span>Student & Home Use</li>
                        </ul>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-orange-600 font-semibold group-hover:translate-x-1 transition">
                            Ask for models <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-red-200 hover:shadow-2xl transition">
                    <img src="https://ipmgroupuk.com/wp-content/uploads/2024/07/4K-Cameras.webp" alt="CCTV Cameras" class="w-full h-44 object-cover" loading="lazy">
                    <div class="p-7">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
                                <i data-lucide="video" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold">CCTV Cameras</h3>
                        </div>
                        <ul class="mt-4 text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-red-600 rounded-full"></span>Indoor & Outdoor Cameras</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-red-600 rounded-full"></span>Night Vision Cameras</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-red-600 rounded-full"></span>DVR & NVR Systems</li>
                        </ul>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-red-600 font-semibold group-hover:translate-x-1 transition">
                            Get a site survey <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-teal-200 hover:shadow-2xl transition">
                    <img src="https://images.pexels.com/photos/2881233/pexels-photo-2881233.jpeg" alt="Networking" class="w-full h-44 object-cover" loading="lazy">
                    <div class="p-7">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center">
                                <i data-lucide="wifi" class="w-6 h-6 text-teal-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold">Networking</h3>
                        </div>
                        <ul class="mt-4 text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-teal-600 rounded-full"></span>Routers & Switches</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-teal-600 rounded-full"></span>WiFi Extenders</li>
                            <li class="flex items-center gap-2"><span class="w-2 h-2 bg-teal-600 rounded-full"></span>Network Cables & Tools</li>
                        </ul>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-teal-600 font-semibold group-hover:translate-x-1 transition">
                            Fix dead zones <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <!-- Extra creative card (NEW) -->
                <div class="group bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl overflow-hidden border border-gray-800 hover:shadow-2xl transition">
                    <img src="https://images.pexels.com/photos/356079/pexels-photo-356079.jpeg" alt="Shop Service Desk" class="w-full h-44 object-cover opacity-90" loading="lazy">
                    <div class="p-7">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                                <i data-lucide="wrench" class="w-6 h-6 text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Need Help Choosing?</h3>
                        </div>
                        <p class="mt-3 text-gray-200">
                            Tell us your budget + use case. We’ll recommend the best parts, laptop, or CCTV package.
                        </p>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-blue-300 font-semibold group-hover:translate-x-1 transition">
                            Get free advice <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="services" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-extrabold">Professional Services</h2>
                <p class="text-xl text-gray-600 mt-3 max-w-2xl mx-auto">
                    Complete solutions for all your computing and security needs.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $serviceCards = [
                    ['icon' => 'monitor', 'title' => 'Custom PC Building', 'desc' => 'Tailored gaming, workstation, and home PCs built to your exact specs and budget.'],
                    ['icon' => 'wrench', 'title' => 'PC Repair & Upgrades', 'desc' => 'Diagnosis, repairs, hardware upgrades, cleaning, and optimization services.'],
                    ['icon' => 'laptop', 'title' => 'Laptop Sales & Setup', 'desc' => 'Laptops with complete setup, software install, and configuration.'],
                    ['icon' => 'video', 'title' => 'CCTV Sales', 'desc' => 'Quality CCTV cameras and systems for home and business security.'],
                    ['icon' => 'shield', 'title' => 'CCTV Installation', 'desc' => 'Professional site survey, installation, setup, and user training.'],
                    ['icon' => 'wifi', 'title' => 'Network Setup', 'desc' => 'WiFi installation, router configuration, structured cabling & troubleshooting.'],
                ];
                foreach ($serviceCards as $card):
                ?>
                    <div class="group bg-white p-8 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-2xl transition hover:-translate-y-1">
                        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition">
                            <i data-lucide="<?= e($card['icon']) ?>" class="w-7 h-7 text-blue-600 group-hover:text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold"><?= e($card['title']) ?></h3>
                        <p class="text-gray-600 mt-3 leading-relaxed"><?= e($card['desc']) ?></p>
                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-blue-600 font-semibold group-hover:translate-x-1 transition">
                            Request Quote <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- PC BUILDS -->
    <section id="pc-builds" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-14">
                <h2 class="text-4xl font-extrabold">Custom PC Build Packages</h2>
                <p class="text-lg text-gray-600 mt-3">
                    Professionally built systems tailored to your needs.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Budget Build -->
                <div class="rounded-2xl border-2 border-gray-200 hover:border-blue-600 transition overflow-hidden shadow-sm hover:shadow-xl">

                    <img
                        src="https://images.unsplash.com/photo-1593642634367-d91a135587b5"
                        alt="Budget PC Build"
                        class="w-full h-44 object-cover"
                        loading="lazy">

                    <div class="p-7">

                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                            Budget Build
                        </span>

                        <h3 class="text-2xl font-extrabold mt-4">
                            Office / Home PC
                        </h3>

                        <p class="text-gray-600 mt-2">
                            Perfect for everyday computing, office work, and study use.
                        </p>

                        <ul class="mt-5 space-y-2 text-gray-700">
                            <li>• Intel i3 / AMD Ryzen 3</li>
                            <li>• 8GB RAM</li>
                            <li>• 256GB SSD</li>
                            <li>• Integrated Graphics</li>
                            <li>• Reliable Power Supply</li>
                            <li>• Windows Installation</li>
                        </ul>

                        <a href="#contact"
                            class="mt-6 block w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition text-center font-semibold">
                            Request Build
                        </a>

                    </div>
                </div>

                <!-- Mid Range Gaming -->
                <div class="rounded-2xl border-2 border-blue-600 shadow-xl overflow-hidden relative">

                    <div class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        Recommended
                    </div>

                    <img
                        src="https://images.unsplash.com/photo-1587202372775-e229f172b9d7"
                        alt="Gaming PC"
                        class="w-full h-44 object-cover"
                        loading="lazy">

                    <div class="p-7">

                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
                            Mid-Range Gaming
                        </span>

                        <h3 class="text-2xl font-extrabold mt-4">
                            Gaming / Streaming PC
                        </h3>

                        <p class="text-gray-600 mt-2">
                            Smooth 1080p gaming and excellent performance for content creation.
                        </p>

                        <ul class="mt-5 space-y-2 text-gray-700">
                            <li>• Intel i5 / AMD Ryzen 5</li>
                            <li>• 16GB RAM</li>
                            <li>• 512GB NVMe SSD</li>
                            <li>• RTX / Radeon Gaming GPU</li>
                            <li>• 650W Power Supply</li>
                            <li>• RGB Case & Cooling</li>
                        </ul>

                        <a href="#contact"
                            class="mt-6 block w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition text-center font-semibold">
                            Request Build
                        </a>

                    </div>
                </div>

                <!-- High End -->
                <div class="rounded-2xl border-2 border-gray-200 hover:border-blue-600 transition overflow-hidden shadow-sm hover:shadow-xl">

                    <img
                        src="https://images.unsplash.com/photo-1587831990711-23ca6441447b"
                        alt="High-End PC"
                        class="w-full h-44 object-cover"
                        loading="lazy">

                    <div class="p-7">

                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-semibold">
                            High-End Creator
                        </span>

                        <h3 class="text-2xl font-extrabold mt-4">
                            Professional Workstation
                        </h3>

                        <p class="text-gray-600 mt-2">
                            Designed for video editing, 3D rendering, and high-end gaming.
                        </p>

                        <ul class="mt-5 space-y-2 text-gray-700">
                            <li>• Intel i7 / AMD Ryzen 7</li>
                            <li>• 32GB RAM</li>
                            <li>• 1TB NVMe SSD + HDD Storage</li>
                            <li>• RTX / Radeon High-End GPU</li>
                            <li>• Modular Power Supply</li>
                            <li>• Premium Case & Cooling</li>
                        </ul>

                        <a href="#contact"
                            class="mt-6 block w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition text-center font-semibold">
                            Request Build
                        </a>

                    </div>
                </div>

            </div>

            <p class="text-center text-sm text-gray-500 mt-10">
                Hardware specifications may vary depending on stock availability.
                Contact us for the best configuration based on your needs.
            </p>

        </div>
    </section>

    <!-- CCTV -->
    <section id="cctv" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-extrabold">CCTV Solutions</h2>
                <p class="text-lg text-gray-600 mt-3">Professional security systems for homes and businesses.</p>
            </div>

            <!-- Partner section (NEW) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-8 mb-10">
                <div class="flex flex-col lg:flex-row gap-8 items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-extrabold flex items-center gap-2">
                            <i data-lucide="badge-check" class="w-6 h-6 text-blue-600"></i>
                            CCTV Partners: Hikvision & Uniarch
                        </h3>
                        <p class="text-gray-600 mt-2 leading-relaxed max-w-2xl">
                            We provide CCTV solutions using trusted brands <b>Hikvision</b> and <b>Uniarch</b> — ideal for reliable
                            night vision, clear recording, and stable remote viewing.
                        </p>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 flex items-center gap-3">
                            <img src="./assets/partners/Hikvision.png" alt="Hikvision" class="h-8 w-auto">
                            <span class="font-bold">Hikvision</span>
                        </div>
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 flex items-center gap-3">
                            <img src="./assets/partners/UNV.png" alt="Uniarch" class="h-8 w-auto">
                            <span class="font-bold">Uniarch</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100">
                    <img src="./assets/cctv/h9c.webp" alt="Home CCTV Setup" class="w-full h-56 object-cover" loading="lazy">
                    <div class="p-8">
                        <h3 class="text-2xl font-extrabold flex items-center gap-2">
                            <i data-lucide="video" class="w-7 h-7 text-blue-600"></i>
                            For Home
                        </h3>
                        <ul class="mt-5 space-y-3 text-gray-700">
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>HD night vision cameras for 24/7 monitoring</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Remote viewing via smartphone app</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Motion detection alerts</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Weatherproof outdoor cameras</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Warranty + installation support</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100">
                    <img src="./assets/cctv/h9c.webp" alt="Business CCTV Setup" class="w-full h-56 object-cover" loading="lazy">
                    <div class="p-8">
                        <h3 class="text-2xl font-extrabold flex items-center gap-2">
                            <i data-lucide="shield" class="w-7 h-7 text-blue-600"></i>
                            For Business
                        </h3>
                        <ul class="mt-5 space-y-3 text-gray-700">
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Multi-camera systems with central monitoring</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>High-capacity storage for extended recording</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Smart features (model dependent)</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Professional-grade installation & cable management</li>
                            <li class="flex gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-green-600 mt-0.5"></i>Maintenance support options</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                <h3 class="text-2xl font-extrabold text-center">Our Installation Process</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-extrabold mx-auto">1</div>
                        <h4 class="mt-4 font-bold text-lg">Site Visit</h4>
                        <p class="text-gray-600 mt-2">We assess needs and recommend camera placement.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-extrabold mx-auto">2</div>
                        <h4 class="mt-4 font-bold text-lg">Quotation</h4>
                        <p class="text-gray-600 mt-2">Equipment list + installation cost + timeline.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-extrabold mx-auto">3</div>
                        <h4 class="mt-4 font-bold text-lg">Install & Setup</h4>
                        <p class="text-gray-600 mt-2">Install, test, and train you to use the system.</p>
                    </div>
                </div>
            </div>

            <!-- Gallery (NEW creative section) -->
            <div class="mt-12">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-2xl font-extrabold">Recent Work Gallery</h3>
                        <p class="text-gray-600 mt-1">A few examples of builds & installations.</p>
                    </div>
                    <a href="#contact" class="hidden sm:inline-flex items-center gap-2 text-blue-600 font-semibold">
                        Book a site visit <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <img class="rounded-2xl h-40 md:h-44 w-full object-cover" src="https://scontent.fcmb2-2.fna.fbcdn.net/v/t39.30808-6/540013944_1240731811404657_1127420840593345959_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=7b2446&_nc_eui2=AeGDdO0vamJlNxd1odfEH9p_KYPUpWgr7Uwpg9SlaCvtTAPHIfMtnEdMSu9lUE0SRzA7FpBxCUrpwtrCPOf7CiIK&_nc_ohc=y2Xg14SyKHkQ7kNvwHpB3l9&_nc_oc=AdkAantbvG6sH5uVpOe-0l6pAfwGItbrzTpJeyPOeLxkJDpDJLUn32-snErMfofDcqdy5Grn6D0_LsNSN-AHvktB&_nc_zt=23&_nc_ht=scontent.fcmb2-2.fna&_nc_gid=aVm-ymTrrcLe9rpoodvDhA&_nc_ss=8&oh=00_AfyeO9YILyn4WmwgD3WOHzZSfVa9skFxeQZGAKxUn9_tLg&oe=69AD9D2A" alt="Gallery 1" loading="lazy">
                    <img class="rounded-2xl h-40 md:h-44 w-full object-cover" src="https://scontent.fcmb2-2.fna.fbcdn.net/v/t39.30808-6/493605449_1138347428309763_4426097313928831814_n.jpg?stp=cp6_dst-jpg_tt6&_nc_cat=102&ccb=1-7&_nc_sid=7b2446&_nc_eui2=AeHEWf4JsfZRuq9Rq8xJwbxwKetLnlMgdQ0p60ueUyB1DZrU-jNW-kn7w5-YQjZWZAdt3kIDGtVD5NkpOTyl35gK&_nc_ohc=3wZ8vSf-Y2IQ7kNvwFzwvGD&_nc_oc=Adkk90yOzaP9Fz0aVGXj9wy5Y_Z54FubRTuP-By_hJtbviX60z9_hk81C4Q0lHXgCGictbDsatwt60y6_g5aUA83&_nc_zt=23&_nc_ht=scontent.fcmb2-2.fna&_nc_gid=68W_1Z_EfIaojr0XyBbgqA&_nc_ss=8&oh=00_Afwz5S2c0LL6EerSHPDi8H-4NQaF7vfjs8p0EnjjtMwPmQ&oe=69AD83A9" alt="Gallery 2" loading="lazy">
                    <img class="rounded-2xl h-40 md:h-44 w-full object-cover" src="https://scontent.fcmb2-2.fna.fbcdn.net/v/t39.30808-6/492828894_1137436638400842_3022489891992193373_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=7b2446&_nc_eui2=AeHcxNh0k1O6Ayj0XbbKhOj_UmZwDJWxChNSZnAMlbEKE_cScqHPMuampU0K_w-m_hoaYUsK8FlRDr9wUnd8LVYe&_nc_ohc=ENi6L1FbmbYQ7kNvwHVHUOO&_nc_oc=Adm9qwo598CN3-0TSrGMvsGl-qObLGuOt8kW3BpzY8M1CTQXhz6IY62EWaZwtyvgCfTgPR3qo2mPOEMxetH1MafT&_nc_zt=23&_nc_ht=scontent.fcmb2-2.fna&_nc_gid=vH45yLI48tdexBzR4onUhw&_nc_ss=8&oh=00_Afx-jJkmazJIVcV9qznwU7eH_ZKsUXw94gMok57k_QdBdg&oe=69AD77AE" alt="Gallery 3" loading="lazy">
                    <img class="rounded-2xl h-40 md:h-44 w-full object-cover" src="https://scontent.fcmb2-2.fna.fbcdn.net/v/t39.30808-6/490496216_1129309499213556_4367029763420026751_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=13d280&_nc_eui2=AeFIWNP6H-vTTXRhRmMGa188y4VwEo3J96zLhXASjcn3rGV6vJtjhBgmpTtrKI_Zmbmk7xLYdzYK8bc-HOVHXUxk&_nc_ohc=gi5N6UmL6dYQ7kNvwFaaHt-&_nc_oc=Adl0NSRFmMsrnFv5je4nrYgoeNxZ81bRKo2Y3tWdYpKT84fXONGcdIj0HCzaRwxvYP4B9tbsbizMVaWFgcP54twe&_nc_zt=23&_nc_ht=scontent.fcmb2-2.fna&_nc_gid=O5qARm7sBwKJDAKYMDqi5w&_nc_ss=8&oh=00_AfxsVx1ACr7qXLhNnpAjWFFVIJd_7c429bxday5OjXK1Zw&oe=69AD78D4" alt="Gallery 4" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Heading -->
            <div class="text-center mb-14">
                <!-- Since 2008 badge (NEW) -->
                <div class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 px-4 py-1 rounded-full text-sm font-semibold mb-3">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                    Serving Since 2008
                </div>

                <h2 class="text-4xl font-extrabold">About Us</h2>
                <p class="text-lg text-gray-600 mt-3 max-w-3xl mx-auto">
                    We serve Matara and surrounding areas with reliable computer solutions and security systems —
                    quality products, honest advice, and after-sales support.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <!-- Image -->
                <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-md relative">
                    <img src="./assets/about/shop.jpg" alt="Our shop in Matara" class="w-full h-80 object-cover" loading="lazy">

                    <!-- Overlay badge on image (optional nice touch) -->
                    <div class="absolute bottom-4 left-4 bg-white/95 backdrop-blur border border-gray-100 shadow-lg rounded-xl px-4 py-2">
                        <p class="text-blue-700 font-extrabold text-sm">Trusted Service • Since 2008</p>
                        <p class="text-gray-600 text-xs">Matara, Sri Lanka</p>
                    </div>
                </div>

                <!-- Content -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center bg-gray-50 border border-gray-100 rounded-2xl p-6 hover:shadow-lg transition">
                            <div class="w-14 h-14 bg-blue-100 rounded-full mx-auto flex items-center justify-center">
                                <i data-lucide="shield" class="w-7 h-7 text-blue-600"></i>
                            </div>
                            <h3 class="mt-4 font-bold text-lg">Warranty Support</h3>
                            <p class="text-gray-600 mt-2 text-sm">Manufacturer warranty + our support.</p>
                        </div>

                        <div class="text-center bg-gray-50 border border-gray-100 rounded-2xl p-6 hover:shadow-lg transition">
                            <div class="w-14 h-14 bg-green-100 rounded-full mx-auto flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-7 h-7 text-green-600"></i>
                            </div>
                            <h3 class="mt-4 font-bold text-lg">Quality Work</h3>
                            <p class="text-gray-600 mt-2 text-sm">Clean builds, neat wiring, tested setups.</p>
                        </div>

                        <div class="text-center bg-gray-50 border border-gray-100 rounded-2xl p-6 hover:shadow-lg transition">
                            <div class="w-14 h-14 bg-orange-100 rounded-full mx-auto flex items-center justify-center">
                                <i data-lucide="message-circle" class="w-7 h-7 text-orange-600"></i>
                            </div>
                            <h3 class="mt-4 font-bold text-lg">Friendly Advice</h3>
                            <p class="text-gray-600 mt-2 text-sm">We recommend what fits your needs.</p>
                        </div>
                    </div>

                    <!-- Highlight box -->
                    <div class="mt-8 bg-gradient-to-br from-blue-50 to-white border border-blue-100 rounded-2xl p-6">
                        <div class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-6 h-6 text-blue-700 mt-1"></i>
                            <div>
                                <div class="font-extrabold text-lg">Serving Matara & nearby areas (Since 2008)</div>
                                <div class="text-gray-600 mt-1">
                                    PC builds • Upgrades • CCTV installation • WiFi setups
                                </div>
                            </div>
                        </div>

                        <a href="#contact" class="mt-5 inline-flex items-center gap-2 text-blue-700 font-semibold">
                            Talk to us <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-extrabold">Get In Touch</h2>
                <p class="text-lg text-gray-600 mt-3">Tell us what you need — we’ll reply quickly.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Info -->
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-md">
                    <h3 class="text-2xl font-extrabold">Contact Information</h3>

                    <div class="mt-6 space-y-5">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                                <i data-lucide="phone" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="font-bold">Phone</div>
                                <a class="text-gray-600 hover:text-blue-600" href="<?= e($telLink) ?>"><?= e($biz['phone']) ?></a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                                <i data-lucide="message-circle" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <div class="font-bold">WhatsApp</div>
                                <a class="text-gray-600 hover:text-green-600" href="<?= e($waLink) ?>" target="_blank" rel="noopener"><?= e($biz['whatsapp']) ?></a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center">
                                <i data-lucide="map-pin" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <div>
                                <div class="font-bold">Address</div>
                                <div class="text-gray-600"><?= e($biz['address']) ?></div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                                <i data-lucide="clock" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="font-bold">Opening Hours</div>
                                <div class="text-gray-600"><?= e($biz['hours'][0]) ?></div>
                                <div class="text-gray-600"><?= e($biz['hours'][1]) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional map (replace with your embed) -->
                    <div class="mt-8 rounded-2xl overflow-hidden border border-gray-100">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d837.6078319195394!2d80.53752027753305!3d5.949408545297181!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae13fe371bf9dc3%3A0x8bbd265e41aaf1bf!2sDynamic%20Computer%20Systems!5e1!3m2!1sen!2slk!4v1772601266859!5m2!1sen!2slk" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <!-- Form -->
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-md">
                    <h3 class="text-2xl font-extrabold">Send Us a Message</h3>

                    <?php if ($success): ?>
                        <div id="successBox" class="mt-5 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                            ✅ Thanks! We’ll contact you soon.
                        </div>
                    <?php endif; ?>

                    <?php if ($errors): ?>
                        <div class="mt-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                            <div class="font-bold mb-2">Please fix the following:</div>
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-6 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Name *</label>
                            <input
                                name="name"
                                value="<?= e($old['name']) ?>"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Your name" />
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Phone *</label>
                            <input
                                name="phone"
                                value="<?= e($old['phone']) ?>"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="+94..." />
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email (optional)</label>
                            <input
                                name="email"
                                value="<?= e($old['email']) ?>"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="you@email.com" />
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Service Interested In</label>
                            <select
                                name="service"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="">Select a service</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= e($s) ?>" <?= ($old['service'] === $s) ? 'selected' : '' ?>>
                                        <?= e($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Message *</label>
                            <textarea
                                name="message"
                                required
                                rows="5"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Tell us what you need (PC build specs, CCTV camera count, location, etc.)"><?= e($old['message']) ?></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl hover:bg-blue-700 transition font-extrabold shadow-sm">
                            Send Message
                        </button>

                        <div class="text-xs text-gray-500 text-center">
                            We typically reply within 24 hours.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 flex items-center justify-center">
                        <img src="./assets/logo.png" alt="Dynamic Computer System Logo" class="w-10 h-10 object-contain">
                    </div>
                        <div class="font-extrabold text-white text-lg"><?= e($biz['name']) ?></div>
                    </div>
                    <p class="text-gray-400">Your trusted partner for computers, laptops, and security solutions in Matara.</p>
                    
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#products" class="hover:text-blue-400 transition">Products</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition">Services</a></li>
                        <li><a href="#pc-builds" class="hover:text-blue-400 transition">PC Builds</a></li>
                        <li><a href="#cctv" class="hover:text-blue-400 transition">CCTV</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li><a href="#services" class="hover:text-blue-400 transition">Custom Builds</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition">Repairs</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition">CCTV Installation</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition">Network Setup</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><?= e($biz['address']) ?></li>
                        <li>Phone: <?= e($biz['phone']) ?></li>
                        <li><?= e($biz['hours'][0]) ?></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
                <p class="mb-2">All products subject to availability. Prices may vary based on stock and market conditions.</p>
                <p>&copy; <?= date('Y') ?> WebX Tech Solutions. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Lucide icons
        lucide.createIcons();

        // Mobile menu toggle
        const btn = document.getElementById('mobileMenuBtn');
        const menu = document.getElementById('mobileMenu');
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');

        btn?.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        // Close menu when clicking a link (mobile)
        document.querySelectorAll('#mobileMenu a').forEach(a => {
            a.addEventListener('click', () => {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                }
            });
        });

        // Auto-hide success message after 4 seconds
        const successBox = document.getElementById('successBox');
        if (successBox) {
            setTimeout(() => successBox.classList.add('hidden'), 4000);
        }
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="ERP Login - Lighter Media Group">
    <meta name="author" content="Lighter Media Group">

    <title>ERP - Login</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpg') }}?v=3">

    {{-- Google Font --}}

    {{-- Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f5f5f3] font-['Inter'] text-zinc-900">

    <main class="min-h-screen flex items-center justify-center px-6 py-12">

        <div class="w-full max-w-lg">

            <div class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-[0_25px_80px_rgba(0,0,0,0.08)]">

                {{-- Left Side --}}
                <div class="hidden">

                    {{-- Decorative gradient --}}
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.15),transparent_35%),radial-gradient(circle_at_80%_80%,rgba(255,255,255,0.08),transparent_35%)]"></div>

                    {{-- Grid --}}
                    <div
                        class="absolute inset-0 opacity-[0.08]"
                        style="
                            background-image:
                                linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),
                                linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
                            background-size: 50px 50px;
                        "
                    ></div>

                    <div class="relative z-10 flex h-full flex-col justify-between p-12">

                        {{-- Logo --}}
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white">
                                    <img
                                        src="{{ asset('img/logo.jpg') }}"
                                        class="h-full w-full object-cover"
                                        alt="Lighter Media Group"
                                    >
                                </div>

                                <div>
                                    <p class="text-sm font-bold tracking-[0.2em] text-white">
                                        LIGHTER
                                    </p>
                                    <p class="text-[10px] tracking-[0.25em] text-zinc-400">
                                        MEDIA GROUP
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Main Text --}}
                        <div class="max-w-lg">

                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2">
                                <span class="h-2 w-2 rounded-full bg-white"></span>
                                <span class="text-xs font-medium tracking-wide text-zinc-300">
                                    INTEGRATED DIGITAL ECOSYSTEM
                                </span>
                            </div>

                            <h1 class="text-5xl font-extrabold leading-[1.05] tracking-[-0.04em] text-white xl:text-6xl">
                                Build.
                                <br>
                                Manage.
                                <br>
                                <span class="text-zinc-500">Grow.</span>
                            </h1>

                            <p class="mt-6 max-w-md text-sm leading-7 text-zinc-400">
                                Satu sistem untuk mengelola operasional,
                                data, dan proses bisnis dalam satu ekosistem
                                digital yang terintegrasi.
                            </p>
                        </div>

                        {{-- Bottom --}}
                        <div class="flex items-end justify-between">

                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-500">
                                    Enterprise Resource Planning
                                </p>

                                <p class="mt-2 text-sm font-medium text-zinc-300">
                                    Lighter Media Group
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-3xl font-bold text-white">
                                    ERP
                                </p>
                                <p class="text-xs text-zinc-500">
                                    SYSTEM
                                </p>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- Right Side --}}
                <div class="flex min-h-[640px] items-center justify-center p-8 sm:p-12">

                    <div class="w-full max-w-md">

                        {{-- Mobile Logo --}}
                        <div class="mb-10">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white">
                                    <img
                                        src="{{ asset('img/logo.jpg') }}"
                                        class="h-full w-full object-cover"
                                        alt="Lighter Media Group"
                                    >
                                </div>

                                <div>
                                    <p class="text-sm font-bold tracking-[0.2em]">
                                        LIGHTER
                                    </p>
                                    <p class="text-[10px] tracking-[0.25em] text-zinc-400">
                                        MEDIA GROUP
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Heading --}}
                        <div class="mb-10">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                Welcome Back
                            </p>

                            <h2 class="text-3xl font-bold tracking-[-0.03em] text-zinc-950 sm:text-4xl">
                                Sign in to your account
                            </h2>

                            <p class="mt-3 text-sm leading-6 text-zinc-500">
                                Enter your credentials to access the ERP system.
                            </p>
                        </div>

                        {{-- Form --}}
                        <form
                            id="loginForm"
                            method="POST"
                            action="{{ route('post-login') }}"
                            class="space-y-6"
                        >
                            @csrf

                            {{-- Email --}}
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-semibold text-zinc-800"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="you@company.com"
                                    autocomplete="email"
                                    autofocus
                                    required
                                    class="block w-full rounded-xl border bg-zinc-50 px-4 py-3.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400
                                    @error('email')
                                        border-red-500 ring-4 ring-red-500/10
                                    @else
                                        border-zinc-200 focus:border-zinc-950 focus:bg-white focus:ring-4 focus:ring-zinc-950/5
                                    @enderror"
                                >

                                @error('email')
                                    <p class="mt-2 text-xs font-medium text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Password --}} 
                            <div> 
                                <div class="mb-2 flex items-center justify-between"> 
                                    <label for="password" class="block text-sm font-semibold text-zinc-800" > Password </label> 
                                </div> 
                                <div class="relative"> 
                                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required class="block w-full rounded-xl border bg-zinc-50 px-4 py-3.5 pr-12 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 @error('password') border-red-500 ring-4 ring-red-500/10 @else border-zinc-200 focus:border-zinc-950 focus:bg-white focus:ring-4 focus:ring-zinc-950/5 @enderror" > 
                                    {{-- Toggle Password --}} 
                                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-zinc-400 transition hover:text-zinc-700" aria-label="Show password" > 
                                        {{-- Eye --}} 
                                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5" > <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 5 12 5c4.64 0 8.577 2.51 9.964 6.678.041.123.041.255 0 .378C20.577 16.49 16.64 19 12 19c-4.64 0-8.577-2.51-9.964-6.678Z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /> </svg> 
                                    </button> 
                                    @error('password') 
                                    <p class="mt-2 text-xs font-medium text-red-500"> {{ $message }} </p> 
                                    @enderror 
                                </div> 
                            </div>

                            {{-- Submit --}}
                            <button
                                id="loginButton"
                                type="submit"
                                class="group flex w-full items-center justify-center gap-3 rounded-xl bg-black px-5 py-3.5 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-zinc-800 hover:shadow-xl active:translate-y-0"
                            >
                                <span id="loginButtonLabel">Sign In</span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                    />
                                </svg>
                            </button>

                        </form>

                        {{-- Footer --}}
                        <div class="mt-10 border-t border-zinc-100 pt-6">
                            <p class="text-center text-xs text-zinc-400">
                                © {{ date('Y') }} Lighter Media Group.
                                All rights reserved.
                            </p>
                            <p class="mt-3 text-center text-xs text-zinc-400">
                                <a class="underline transition hover:text-zinc-700" href="{{ route('legal.privacy') }}">Kebijakan Privasi</a>
                                <span aria-hidden="true"> · </span>
                                <a class="underline transition hover:text-zinc-700" href="{{ route('legal.terms') }}">Ketentuan Layanan</a>
                                <span aria-hidden="true"> · </span>
                                <a class="underline transition hover:text-zinc-700" href="{{ route('legal.data-deletion') }}">Penghapusan Data</a>
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </main>

    <script> 
        const togglePassword = document.getElementById('togglePassword'); 
        const passwordInput = document.getElementById('password'); 
        const eyeIcon = document.getElementById('eyeIcon'); 
        
        togglePassword.addEventListener('click', function () { 
            const isPassword = passwordInput.type === 'password'; 
            passwordInput.type = isPassword ? 'text' : 'password'; 
            togglePassword.setAttribute( 'aria-label', isPassword ? 'Hide password' : 'Show password' ); 
            if (isPassword) { 
                eyeIcon.innerHTML = ` <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 2.036 12.322C3.423 16.49 7.36 19 12 19c1.55 0 3.007-.337 4.31-.938M6.228 6.228A10.45 10.45 0 0 1 12 5c4.64 0 8.577 2.51 9.964 6.678a1.012 1.012 0 0 1 0 .644 10.5 10.5 0 0 1-1.875 3.348M6.228 6.228 3 3m3.228 3.228 3.04 3.04m8.504 8.504L21 21m-3.228-3.228-3.04-3.04m0 0a3 3 0 1 1-4.243-4.243m4.243 4.243L9.73 9.73" /> `; 
            } else { 
                eyeIcon.innerHTML = ` <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 5 12 5c4.64 0 8.577 2.51 9.964 6.678.041.123.041.255 0 .378C20.577 16.49 16.64 19 12 19c-4.64 0-8.577-2.51-9.964-6.678Z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /> `; 
            } 
        }); 

        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonLabel = document.getElementById('loginButtonLabel');

        function resetLoginButton() {
            loginButton.disabled = false;
            loginButton.classList.remove('cursor-wait', 'opacity-80');
            loginButtonLabel.textContent = 'Sign In';
        }

        loginForm.addEventListener('submit', function () {
            loginButton.disabled = true;
            loginButton.classList.add('cursor-wait', 'opacity-80');
            loginButtonLabel.textContent = 'Signing in…';
        });

        window.addEventListener('pageshow', resetLoginButton);
    </script>

</body>

</html>

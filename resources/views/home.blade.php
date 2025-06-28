@extends('layouts.panel')
@section('title', __('Dashboard'))
@section('content')
    <div class="container_words">

        <h1 class="word big-welcome">Bienvenid@</h1>
        <h2 class="word">Welcome</h2>
        <h2 class="word">
            <div style="width:100px;height:150px">
                <img style="width:100%;object-fit:contain"
                    src="https://i.pinimg.com/originals/c0/46/05/c0460581a0d5ef331a3bc98ec24546e5.png" alt="">
            </div>
        </h2>
        <h2 class="word">Bienvenue</h2>
        <h2 class="word">Willkommen</h2>
        <h2 class="word">Benvenuto</h2>
        <h2 class="word">Bem-vindo</h2>
        <h2 class="word">Добро пожаловать</h2>
        <h2 class="word">欢迎</h2>
        <h2 class="word">ようこそ</h2>
        <h2 class="word">أهلاً وسهلاً</h2>
        <h2 class="word">환영합니다</h2>
        <h2 class="word">स्वागत है</h2>
        <h2 class="word">স্বাগত</h2>
        <h2 class="word">Hoş geldiniz</h2>
        <h2 class="word">Selamat datang</h2>
        <h2 class="word">Chào mừng</h2>
        <h2 class="word">ยินดีต้อนรับ</h2>
        <h2 class="word">ברוך הבא</h2>
        <h2 class="word">Καλώς ορίσατε</h2>
        <h2 class="word">Witamy</h2>
        <h2 class="word">Välkommen</h2>
        <h2 class="word">Welkom</h2>
        <h2 class="word">Velkommen</h2>
        <h2 class="word">Tervetuloa</h2>
        <h2 class="word">Üdvözöljük</h2>
        <h2 class="word">Vítejte</h2>
        <h2 class="word">Vitajte</h2>
        <h2 class="word">Bun venit</h2>
        <h2 class="word">Ласкаво просимо</h2>

    </div>

    <script>
        const container = document.querySelector('.container_words');
        const words = document.querySelectorAll('.word');

        words.forEach(word => {
            const wordWidth = word.offsetWidth;
            const wordHeight = word.offsetHeight;
            const containerWidth = container.offsetWidth;
            const containerHeight = container.offsetHeight;

            let x = Math.random() * (containerWidth - wordWidth);
            let y = Math.random() * (containerHeight - wordHeight);
            let dx = Math.random() * 3 - 1;
            let dy = Math.random() * 2 - 1;

            function move() {
                x += dx;
                y += dy;

                if (x < 0 || x + wordWidth > containerWidth) {
                    dx *= -1;
                }
                if (y < 0 || y + wordHeight > containerHeight) {
                    dy *= -1;
                }

                word.style.transform = `translate(${x}px, ${y}px)`;

                requestAnimationFrame(move);
            }

            move();
        });
    </script>
@endsection

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Monster Battle</title>

   <link rel="stylesheet" href="{{ asset('/app.css') }}">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <nav class="tavern-nav" aria-label="Navegación principal">
        <div class="tavern-nav__plank">
            <ul class="tavern-nav__list">
                <li><a class="tavern-nav__link @if(request()->is('monster')) is-active @endif"
                        href="/monster">Monsters</a></li>              
              

            </ul>
        </div>
    </nav>


    @if (session('success'))
        <div class="parchment-flash parchment-flash--success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="parchment-flash parchment-flash--error">
            {{ session('error') }}
        </div>
    @endif

    <div class="sheet">
       

        

        <main>
            {{ $slot }}
        </main>

         

       

    </div>

   

</body>

</html>
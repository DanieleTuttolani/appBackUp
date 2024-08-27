<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Gestione backup dominio</title>

    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

</head>

<body class="antialiased">

    <div
        class="relative min-h-screen bg-dots-darker pt-10 px-3 bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <h1 class="text-6xl text-blue-700 mt-10">Inserisci Dominio</h1>

        <div class="flex sm:justify-center sm:items-center">

            {{-- insert --}}
            <div class="bg-white rounded p-3">
                <p class="text-center text-xl">Inserisci un dominio</p>

                <form id="newDomain" class="flex flex-col">
                    @csrf
                    <input class="rounded my-2 p-2 border border-gray-400" type="text" name="user_name"
                        placeholder="Nome utente">
                    <input class="rounded my-2 p-2 border border-gray-400" type="text" name="database_name"
                        placeholder="Nome DB">
                    <input class="rounded my-2 p-2 border border-gray-400" type="text" name="domain_name"
                        placeholder="Nome Dominio">
                    <input class="rounded my-2 p-2 border border-gray-400" type="text" name="ip"
                        placeholder="IP">
                    <input class="rounded my-2 p-2 border border-gray-400" type="password" name="password"
                        placeholder="Password">

                    <label for="backup_rate_time">Rateo Backup</label>
                    <input class="rounded my-2 p-2 border border-gray-400" type="time" name="backup_rate_time"
                        id="backup_rate_time" placeholder="Refresh Backup">

                    <div class="text-center">
                        <button type="button" class="bg-blue-600 px-3 py-1 rounded text-xl mt-2 text-white"
                            onclick="storeDomain()" id="save">Inserisci</button>
                    </div>
                </form>


            </div>
        </div>

        {{-- sezione con contatore e bottone per creazione massiva --}}
        <div class="flex justify-between px-4">
            <h1 class="text-6xl text-blue-700 mt-32">Domini Inseriti: {{ count($domains) }}</h1>

            <button id="massCreation" class="bg-blue-700 h-16 text-white text-3xl mt-32 rounded px-3 py-2"
                onclick="massBackupCreation()">
                Creazione Backup Massiva
            </button>
        </div>
        {{-- lista --}}
        <div class="flex justify-evenly pt-12">

            @foreach ($domains as $domain)
                <div class="flex sm:justify-center sm:items-center">

                    {{-- insert --}}
                    <div class="bg-white rounded p-3">
                        <p class="text-center text-xl">{{ $domain->name }}</p>

                        <form id="updateDomain" action="{{ route('updateDomain') }}" method="POST"
                            class="flex flex-col">
                            @csrf
                            @method('PUT')
                            <input class="rounded my-2 p-2 border border-gray-400" type="text" name="user_name"
                                id="name" placeholder="Nome utente" disabled value="{{ $domain->user_name }}">
                            <input class="rounded my-2 p-2 border border-gray-400" type="text" name="db_name"
                                id="db_name" placeholder="Nome DB" disabled value="{{ $domain->database_name }}">
                            <input class="rounded my-2 p-2 border border-gray-400" type="text" name="dom_name"
                                id="dom_name" placeholder="Nome Dominio" disabled value="{{ $domain->domain_name }}">

                            <input class="rounded my-2 p-2 border border-gray-400" type="text" name="ip"
                                id="ip" placeholder="IP" disabled value="{{ $domain->ip }}">

                            <label for="backup_rate_time">
                                Rateo Backup <span class="text-xs">(Aggiorna)</span>
                            </label>
                            <input class="rounded my-2 p-2 border border-gray-400" type="time"
                                name="backup_rate_time" id="backup_rate_time" placeholder="Refresh Backup"
                                value="{{ $domain->backup_rate_time }}">

                            <div class="text-center">
                                <button type="button" onclick="updateDomain()"
                                    class="bg-blue-600 px-3 py-1 rounded text-xl mt-2 text-white"
                                    id="update">Modifica</button>
                            </div>
                            {{-- dati nascosi --}}
                            <input type="hidden" name="domain_id" value="{{ $domain->id }}">
                        </form>


                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Inclusione del CSRF token per Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const storeDomain = () => {
            const form = document.getElementById('newDomain');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Raccogli i dati del modulo
            const formData = new FormData(form);

            // Invio dei dati con Axios
            axios.post('/domains/insert-domain', formData, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(response => {})
                .catch(error => {});
        };
        // Collega la funzione `storeDomain` al pulsante
        document.getElementById('save').addEventListener('click', storeDomain);

        const updateDomain = () => {
            const form = document.getElementById('updateDomain');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const formData = new FormData(form);

            axios({
                    method: 'POST',
                    url: '/domains/update-domain',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(response => {

                    if (response.data === 'ok') {

                        window.location.href = window.location.href
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        }

        document.getElementById('update').addEventListener('click', updateDomain);



        const massBackupCreation = () => {
            axios.get('/domains/mass-backup-creation')
                .then(response => {

                    if (response.data === 'ok') {
                        alert(
                            'backup creati per ogni dominio! controlla la cartella storage/app/backup'
                        )
                    } else {
                        alert('errore nella crezione dei backup');
                    }
                })
        }

        document.getElementById('massCreation').addEventListener('click', massBackupCreation);

    });
</script>

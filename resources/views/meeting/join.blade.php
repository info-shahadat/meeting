@extends('layouts.master')
@section('title', 'Softvence :: Join')
@section('page-title', 'Join')

@section('content')

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="#">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item"><a href="#">Softvence</a></li>
            <li class="breadcrumb-item active" aria-current="page">Join</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <input id="name" class="form-control" placeholder="Enter your name">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" onclick="start()">Join</button>
    </div>
</div>

<div id="jitsi" style="height: 80vh; display:none;"></div>

@endsection

@section('script')

<script src="https://meet.jit.si/external_api.js"></script>
<script>
let participantId = null;
let api = null;

window.start = function() {
    const name = document.getElementById('name').value.trim();
    if (!name) {
        alert('Enter your name');
        return;
    }

    // Register participant in backend
    fetch(`/meet/{{ $meeting->room }}/join`, {
        method: 'POST',
        headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ name })
    })
    .then(r => r.json())
    .then(data => {
        participantId = data.id;

        // Initialize Jitsi
        api = new JitsiMeetExternalAPI("meet.jit.si", {
            roomName: "{{ $meeting->room }}",
            parentNode: document.getElementById('jitsi'),

            userInfo: { displayName: name },

            configOverwrite: {
                enableWelcomePage: false,
                enableSpeakerStats: false,
                disableDeepLinking: true,
                disableRemoteControl: true,
            },

            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone',
                    'camera',
                    'desktop',
                    'chat',
                    'fullscreen',
                    'hangup'
                ]
            }
        });

        document.getElementById('jitsi').style.display = 'block';

        api.addListener('readyToClose', () => leave());
    })
    .catch(err => {
        console.error(err);
        alert('Failed to join meeting');
    });
};

window.leave = function() {
    if (!participantId) return;

    fetch(`/meet/{{ $meeting->room }}/leave`, {
        method: 'POST',
        headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ id: participantId })
    });

    if (api) {
        api.dispose();
        api = null;
        document.getElementById('jitsi').style.display = 'none';
    }
};
</script>

@endsection

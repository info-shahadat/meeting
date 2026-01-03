<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Join Meeting - {{ $meeting->title }}</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="{{ asset('css/join.css') }}">

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="https://8x8.vc/vpaas-magic-cookie-b17792c83b414744bcb1e756f65beb2e/external_api.js" async></script>
</head>

<body>

<div class="join-card-container" id="join-section">
    <div class="join-card">

        <h4 class="mb-4 text-center">
            Join Meeting: <strong>{{ $meeting->title }}</strong>
        </h4>

        <div class="mb-3 text-center">
            <label class="form-label">Share this meeting link:</label>
            <div class="input-group">
                <input type="text" id="meetingLink" class="form-control" value="{{ url('/meet/'.$meeting->room) }}" readonly>
                <button class="btn btn-outline-primary" onclick="copyLink()">Copy</button>
            </div>
            <small id="copyAlert" class="d-none text-success">Link copied!</small>
        </div>

        @guest
            <!-- Guest Inputs -->
            <div class="mb-3 text-center">
                <p class="text-center text-info mb-3">Enter your name/email or continue with Google</p>

                <!-- Name & Email -->
                <div class="row g-2 mb-3 justify-content-center">
                    <div class="col-md-5">
                        <input id="name" class="form-control" placeholder="Enter your name">
                    </div>
                    <div class="col-md-5">
                        <input id="email" type="email" class="form-control" placeholder="Enter your Gmail">
                    </div>
                </div>

                <!-- OR Separator -->
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <hr class="flex-grow-1" style="border-top:1px solid #ccc;">
                    <span class="mx-2 text-muted fw-bold">OR</span>
                    <hr class="flex-grow-1" style="border-top:1px solid #ccc;">
                </div>

                <!-- Google Button -->
                <div id="googleBtn" class="d-flex justify-content-center"></div>
            </div>
        @else
            <p class="text-center text-info mb-3">
                Joining as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
            </p>
        @endguest

        <div class="text-center mt-3">
            <button class="btn btn-primary px-5 py-2 fw-bold" onclick="start()">Join</button>
        </div>
    </div>
</div>

<div id="jaas-container" style="display:none;"></div>

<script>
/* Google Init */
window.onload = () => {
    if (document.getElementById('googleBtn')) {
        google.accounts.id.initialize({
            client_id: "{{ config('services.google.client_id') }}",
            callback: handleGoogleLogin
        });

        google.accounts.id.renderButton(
            document.getElementById('googleBtn'),
            { theme: "outline", size: "large" }
        );
    }
};

function handleGoogleLogin(response) {
    const data = parseJwt(response.credential);
    if (!data.email || !data.name) {
        alert('Google login failed');
        return;
    }

    document.getElementById('name').value = data.name;
    document.getElementById('email').value = data.email;

    start();
}

function parseJwt(token) {
    const base64 = token.split('.')[1].replace(/-/g,'+').replace(/_/g,'/');
    const padded = base64.padEnd(base64.length + (4 - base64.length % 4) % 4, '=');
    return JSON.parse(atob(padded));
}

/* Copy Link */
function copyLink() {
    const input = document.getElementById('meetingLink');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        const alert = document.getElementById('copyAlert');
        alert.classList.remove('d-none');
        setTimeout(() => alert.classList.add('d-none'), 2000);
    });
}

/* Start Meeting */
let participantId = null;
let api = null;

function start() {
    let guestName, guestEmail;

    @guest
        guestName = document.getElementById('name').value.trim();
        guestEmail = document.getElementById('email').value.trim();
        if (!guestName || !guestEmail) {
            alert('Enter name and email');
            return;
        }
    @else
        guestName = "{{ auth()->user()->name }}";
        guestEmail = "{{ auth()->user()->email }}";
    @endguest

    fetch("{{ route('meet.recordJoin', $meeting->room) }}", {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({ name: guestName, email: guestEmail })
    })
    .then(res => res.json())
    .then(data => {
        participantId = data.id;

        api = new JitsiMeetExternalAPI("8x8.vc", {
            roomName: "vpaas-magic-cookie-b17792c83b414744bcb1e756f65beb2e/{{ $meeting->room }}",
            parentNode: document.getElementById('jaas-container'),
            width: '100%',
            height: '100%',
            userInfo: { displayName: guestName, email: guestEmail }
        });

        document.getElementById('join-section').style.display = 'none';
        document.getElementById('jaas-container').style.display = 'block';

        api.addListener('readyToClose', leave);
    })
    .catch(() => alert('Failed to join meeting'));
}

function leave() {
    if (!participantId) return;

    fetch("{{ route('meet.recordLeave', $meeting->room) }}", {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({ id: participantId })
    });

    if (api) api.dispose();
}
</script>

</body>
</html>

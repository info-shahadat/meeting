<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Meeting - {{ $meeting->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        #jaas-container {
            display: none;
            height: 100vh;
        }
        .join-card {
            margin-top: 50px;
        }
    </style>

    <!-- JAAS API -->
    <script src="https://8x8.vc/vpaas-magic-cookie-b17792c83b414744bcb1e756f65beb2e/external_api.js" async></script>
</head>
<body>

<div class="container join-card" id="join-section">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-4 text-center">Join Meeting: <strong>{{ $meeting->title }}</strong></h4>

                    {{-- Share Meeting Link --}}
                    <div class="mb-3 text-center">
                        <label class="form-label">Share this meeting link:</label>
                        <div class="input-group">
                            <input type="text" id="meetingLink" class="form-control"
                                   value="{{ url('/meet/'.$meeting->room) }}" readonly>
                            <button class="btn btn-outline-primary" onclick="copyLink()">Copy</button>
                        </div>
                        <small id="copyAlert" class="text-success d-none">Link copied!</small>
                    </div>

                    {{-- Guest users --}}
                    @guest
                        <p class="text-center text-muted mb-3">Please join with your Google account</p>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <input id="name" class="form-control" placeholder="Enter your name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <input id="email" type="email" class="form-control" placeholder="Enter your Gmail">
                            </div>
                        </div>
                    @else
                        {{-- Authenticated users --}}
                        <p class="text-center text-muted mb-3">
                            You are joining as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                        </p>
                    @endguest

                    <div class="text-center">
                        <button class="btn btn-primary px-5" onclick="start()">Join</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="jaas-container"></div>

<!-- Floating Share Button (hidden initially) -->
<button id="shareBtn" class="btn btn-primary"
        style="position: fixed; top: 20px; left: 20px; z-index: 9999; display: none;"
        onclick="shareLink()">
    Share Meeting Link
</button>

<script>
    let participantId = null;
    let api = null;

    // Determine name & email
    @guest
        let guestName = '';
        let guestEmail = '';
    @endguest

    function start() {
        @guest
            guestName = document.getElementById('name').value.trim();
            guestEmail = document.getElementById('email').value.trim();
            if (!guestName || !guestEmail) {
                alert('Enter your name and Gmail to join');
                return;
            }
        @else
            guestName = "{{ auth()->user()->name }}";
            guestEmail = "{{ auth()->user()->email }}";
        @endguest

        // Save participant in backend
        fetch("{{ route('meet.recordJoin', $meeting->room) }}", {
            method: 'POST',
            headers: {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: guestName, email: guestEmail })
        })
        .then(res => res.json())
        .then(data => {
            participantId = data.id;

            // Initialize JAAS
            api = new JitsiMeetExternalAPI("8x8.vc", {
                roomName: "vpaas-magic-cookie-b17792c83b414744bcb1e756f65beb2e/{{ $meeting->room }}",
                parentNode: document.getElementById('jaas-container'),
                userInfo: { displayName: guestName, email: guestEmail },
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
                },
            });

            // Show JAAS container
            document.getElementById('jaas-container').style.display = 'block';
            document.getElementById('join-section').style.display = 'none';

            // Show floating share button
            document.getElementById('shareBtn').style.display = 'block';

            // Listener for leave
            api.addListener('readyToClose', () => leave());
        })
        .catch(err => {
            console.error(err);
            alert('Failed to join meeting');
        });
    }

    // Leave function
    function leave() {
        if (!participantId) return;

        fetch("{{ route('meet.recordLeave', $meeting->room) }}", {
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
        }

        document.getElementById('jaas-container').style.display = 'none';
        document.getElementById('join-section').style.display = 'block';
        document.getElementById('shareBtn').style.display = 'none';
    }

    // Share link function
    function shareLink() {
        const meetingURL = "{{ url('/meet/'.$meeting->room) }}";

        if (navigator.clipboard) {
            navigator.clipboard.writeText(meetingURL).then(() => {
                alert('Meeting link copied to clipboard!');
            });
        } else {
            // fallback for older browsers
            const tempInput = document.createElement('input');
            tempInput.value = meetingURL;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Meeting link copied to clipboard!');
        }
    }
</script>

</body>
</html>

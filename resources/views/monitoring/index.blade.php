@extends('layout.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 rounded-2xl bg-zinc-950 px-6 py-7 text-white">
        <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-zinc-400">View-only browser monitoring</p>
        <h1 class="mt-2 text-3xl font-semibold">Live Monitoring</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">Streaming layar dan kamera berlangsung langsung dari browser target. Tidak ada kontrol perangkat dan target harus menyetujui izin pada setiap sesi.</p>
    </div>
    <div class="grid gap-6 xl:grid-cols-[330px_minmax(0,1fr)]">
        <aside id="monitoring-user-picker" class="hidden rounded-2xl border border-zinc-200 bg-white p-4">
            <h2 class="font-semibold text-zinc-950">Pengguna</h2>
            <div class="mt-4 max-h-[65vh] space-y-2 overflow-y-auto">
            @foreach($users as $user)
                <a href="{{ route('monitoring.index',['user'=>$user->id]) }}" class="flex items-center gap-3 rounded-xl border px-3 py-3 {{ $target->id===$user->id ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 hover:border-zinc-400' }}">
                    <span class="grid h-9 w-9 place-items-center rounded-full {{ $target->id===$user->id ? 'bg-white/15' : 'bg-zinc-100' }} font-semibold">{{ strtoupper(mb_substr($user->name ?: $user->email,0,1)) }}</span>
                    <span class="min-w-0"><b class="block truncate text-sm">{{ $user->name ?: 'Tanpa nama' }}</b><small class="block truncate {{ $target->id===$user->id ? 'text-zinc-300' : 'text-zinc-500' }}">{{ $user->email }}</small></span>
                </a>
            @endforeach
            </div>
        </aside>
        <section id="monitoring-workspace" class="col-span-full rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Target</p><h2 class="mt-1 text-xl font-semibold text-zinc-950">{{ $target->name ?: $target->email }}</h2><p class="text-sm text-zinc-500">{{ $target->email }}</p></div>
                <span id="live-status" class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">{{ $activeSession ? 'Target sedang online' : 'Belum ada sesi live' }}</span>
            </div>

            @if($canStart)
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <b>Mulai dari perangkat target</b>
                <p class="mt-1 leading-6">Pilih satu window kerja yang ingin dibagikan. Admin hanya dapat melihat, bukan mengendalikan perangkat Anda.</p>
                <div class="mt-4 flex flex-wrap gap-2"><button id="start-live" class="rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white">1. Izinkan window kerja</button><button id="allow-camera" class="hidden rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white">2. Izinkan kamera</button><button id="stop-live" class="hidden rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700">Hentikan</button></div>
                <p id="notice" class="mt-3 hidden text-xs font-semibold"></p>
            </div>
            @elseif($canView)
                <div id="viewer-actions" class="mt-6">
                    <div class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600">{{ $activeSession ? 'Menghubungkan live monitoring...' : 'Menunggu target memulai monitoring...' }}</div>
                </div>
            @else
                <div class="mt-6 rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600">Hanya admin yang dapat melihat live monitoring pengguna lain.</div>
            @endif

            <div id="video-grid" class="mt-6 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(280px,360px)]">
                <div><p class="mb-2 text-xs font-semibold text-zinc-500">PREVIEW KERJA</p><video id="screen-video" autoplay playsinline muted class="hidden aspect-video w-full rounded-xl bg-zinc-950 object-contain"></video><div id="screen-empty" class="grid aspect-video place-items-center rounded-xl bg-zinc-100 text-sm text-zinc-500">Preview kerja akan muncul di sini.</div></div>
                <div><div class="mb-2 flex items-center justify-between gap-2"><p class="text-xs font-semibold text-zinc-500">KAMERA</p><button id="toggle-camera-blur" type="button" class="hidden rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-100">Aktifkan blur</button></div><video id="camera-video" autoplay playsinline muted class="hidden aspect-video w-full rounded-xl bg-zinc-950 object-cover"></video><div id="camera-empty" class="grid aspect-video place-items-center rounded-xl bg-zinc-100 text-sm text-zinc-500">Kamera belum aktif.</div></div>
            </div>
        </section>
    </div>

</div>

@endsection

@push('script')
<script>
(() => {
 const csrf='{{ csrf_token() }}', isTarget={{ $canStart ? 'true':'false' }}, canView={{ $canView ? 'true':'false' }}, sessionId={{ $activeSession?->id ?? 'null' }}, targetId={{ $target->id }};
 const screen=document.getElementById('screen-video'), camera=document.getElementById('camera-video'), notice=document.getElementById('notice'), status=document.getElementById('live-status');
 let session=sessionId, pc, screenStream, cameraStream, cameraPublishedStream, cameraSourceVideo, cameraCanvas, cameraFrame, cameraBlurred=false, poller, pendingCandidates=[], pollDelay=300, iceServers=[], iceRequest;
 const url=(name,id)=>({start:'{{ route('monitoring.sessions.start') }}',join:'{{ url('monitoring/sessions') }}/'+id+'/join',ice:'{{ url('monitoring/sessions') }}/'+id+'/ice-servers',signal:'{{ url('monitoring/sessions') }}/'+id+'/signal',signals:'{{ url('monitoring/sessions') }}/'+id+'/signals',stop:'{{ url('monitoring/sessions') }}/'+id+'/stop'})[name];
 const say=(text,bad=false)=>{if(!notice)return;notice.textContent=text;notice.className='mt-3 text-xs font-semibold '+(bad?'text-red-700':'text-emerald-700')};
 const show=(video,empty,stream)=>{video.srcObject=stream;video.classList.remove('hidden');document.getElementById(empty).classList.add('hidden')};
 const waitForVideo=video=>new Promise((resolve,reject)=>{video.onloadedmetadata=resolve;video.onerror=reject;setTimeout(resolve,3000)});
 const post=async(u,body={})=>{let r=await fetch(u,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(body)});if(!r.ok)throw new Error((await r.json().catch(()=>({}))).message||'Permintaan gagal.');return r.json()};
 const send=(type,payload)=>post(url('signal',session),{type,payload});
 async function loadIceServers(){
   if(iceServers.length)return iceServers;
   if(!iceRequest)iceRequest=fetch(url('ice',session),{headers:{Accept:'application/json'}}).then(async response=>{
     const data=await response.json().catch(()=>({}));
     if(!response.ok||!Array.isArray(data.iceServers))throw new Error(data.message||'Cloudflare TURN belum siap.');
     iceServers=data.iceServers;
     return iceServers;
   }).finally(()=>iceRequest=null);
   return iceRequest;
 }
 const description=payload=>({
   ...payload,
   sdp: typeof payload?.sdp==='string'
     ? payload.sdp.replace(/\r?\n/g,'\r\n').replace(/\r\n?$/,'')+'\r\n'
     : payload?.sdp,
 });
 function peer(target){
   if(pc)return pc;
   pc=new RTCPeerConnection({iceServers});
   pc.onicecandidate=e=>{if(e.candidate)send('candidate',e.candidate.toJSON()).catch(()=>{})};
   pc.onconnectionstatechange=()=>{
     if(pc.connectionState==='connected'){
       status.textContent='Live monitoring terhubung';
       status.className='rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
       pollDelay=2500;
       beginPoll();
     }
     if(['failed','disconnected'].includes(pc.connectionState)){
       status.textContent='Koneksi live gagal';
     }
   };
   if(!target){
     // The target publishes one work preview and one camera stream.
     pc.addTransceiver('video',{direction:'recvonly'});
     pc.addTransceiver('video',{direction:'recvonly'});
     pc.ontrack=e=>{
       const media=new MediaStream([e.track]);
       if(e.transceiver.mid==='0')show(screen,'screen-empty',media);
       else show(camera,'camera-empty',media);
     };
   }
   return pc;
 }
 function publishTrack(connection, track, stream){
   const sender=connection.getSenders().find(item=>item.track?.id===track.id);
   return sender||connection.addTrack(track,stream);
 }
 async function flushCandidates(){if(!pc||!pc.remoteDescription)return;while(pendingCandidates.length){try{await pc.addIceCandidate(pendingCandidates.shift())}catch(e){}}}
 async function handleSignals(){
   if(!session)return; try{let response=await fetch(url('signals',session),{headers:{Accept:'application/json'}});if(!response.ok)throw new Error('Sinyal monitoring tidak dapat dimuat.');let data=await response.json();if(data.status!=='active')return end(false);
   for(const signal of data.signals){let p=signal.payload||{}; if(signal.type==='ready'&&isTarget){await negotiate()}
   else if(signal.type==='offer'&&!isTarget){let c=peer(false);await c.setRemoteDescription(description(p));await flushCandidates();let answer=await c.createAnswer();await c.setLocalDescription(answer);await send('answer',c.localDescription)}
   else if(signal.type==='answer'&&isTarget){await peer(true).setRemoteDescription(description(p));await flushCandidates()}
   else if(signal.type==='candidate'){if(!pc||!pc.remoteDescription)pendingCandidates.push(p);else{try{await pc.addIceCandidate(p)}catch(e){}}}}}catch(e){console.error('Monitoring WebRTC error:',e);if(isTarget)say('Koneksi live gagal: '+(e.message||'periksa koneksi browser'),true);else{status.textContent='Koneksi live gagal';const target=document.getElementById('viewer-actions');if(target)target.innerHTML='<div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Koneksi live gagal. '+(e.message||'Silakan mulai ulang share layar target.')+'</div>'}}}
 function beginPoll(){clearInterval(poller);poller=setInterval(handleSignals,pollDelay);handleSignals()}
 async function negotiate(){const c=peer(true);if(c.signalingState!=='stable')throw new Error('Menunggu koneksi sebelumnya selesai.');const offer=await c.createOffer();await c.setLocalDescription(offer);await send('offer',c.localDescription)}
 async function start(){
   if(screenStream)return;
   if(!window.isSecureContext||!navigator.mediaDevices?.getUserMedia)return say('Browser hanya dapat meminta izin kamera melalui HTTPS atau localhost. Buka ERP memakai https://, bukan http://alamat-IP.',true);
   try{screenStream=await navigator.mediaDevices.getDisplayMedia({video:{frameRate:{ideal:10,max:15},width:{ideal:1280,max:1280},height:{ideal:720,max:720},displaySurface:'window'},audio:false,preferCurrentTab:false,selfBrowserSurface:'exclude',surfaceSwitching:'include'});show(screen,'screen-empty',screenStream);screenStream.getVideoTracks()[0].onended=()=>end(true);document.getElementById('start-live').classList.add('hidden');document.getElementById('allow-camera').classList.remove('hidden');say('Window kerja sudah diizinkan. Lanjutkan dengan izin kamera.')}catch(e){say('Izin window tidak diberikan. Pilih window Chrome lalu klik Izinkan pada dialog browser.',true)}}
 function renderCamera(){if(!cameraCanvas||!cameraSourceVideo)return;const context=cameraCanvas.getContext('2d');context.filter=cameraBlurred?'blur(12px)':'none';context.drawImage(cameraSourceVideo,0,0,cameraCanvas.width,cameraCanvas.height);context.filter='none';cameraFrame=requestAnimationFrame(renderCamera)}
 function toggleCameraBlur(){if(!cameraStream)return;cameraBlurred=!cameraBlurred;document.getElementById('toggle-camera-blur').textContent=cameraBlurred?'Nonaktifkan blur':'Aktifkan blur';say(cameraBlurred?'Blur kamera aktif. Tampilan admin juga dibuat blur.':'Blur kamera dinonaktifkan.')}
 async function allowCamera(){
   const allowButton=document.getElementById('allow-camera');
   if(!screenStream||allowButton?.disabled)return;
   if(allowButton)allowButton.disabled=true;
   try{cameraStream=await navigator.mediaDevices.getUserMedia({video:{frameRate:{ideal:10,max:15},width:{ideal:640,max:640},height:{ideal:360,max:360}},audio:false});cameraSourceVideo=document.createElement('video');cameraSourceVideo.muted=true;cameraSourceVideo.playsInline=true;cameraSourceVideo.srcObject=cameraStream;await cameraSourceVideo.play();await waitForVideo(cameraSourceVideo);cameraCanvas=document.createElement('canvas');cameraCanvas.width=cameraSourceVideo.videoWidth||640;cameraCanvas.height=cameraSourceVideo.videoHeight||360;renderCamera();cameraPublishedStream=cameraCanvas.captureStream(15);show(camera,'camera-empty',cameraPublishedStream);let data=await post(url('start'),{consent:true});session=data.session.id;await loadIceServers();let c=peer(true);screenStream.getVideoTracks().forEach(track=>publishTrack(c,track,screenStream));cameraPublishedStream.getVideoTracks().forEach(track=>publishTrack(c,track,cameraPublishedStream));allowButton.classList.add('hidden');document.getElementById('stop-live').classList.remove('hidden');document.getElementById('toggle-camera-blur').classList.remove('hidden');status.textContent='Live monitoring aktif';status.className='rounded-full bg-red-50 px-3 py-2 text-xs font-semibold text-red-700';say('Streaming aktif. Menunggu admin terhubung.')}catch(e){if(allowButton)allowButton.disabled=false;say('Live monitoring tidak dapat dimulai: '+(e.message||'periksa izin kamera dan konfigurasi TURN.'),true)}}
 async function join(){try{await post(url('join',session));await loadIceServers();peer(false);status.textContent='Menghubungkan live stream...';beginPoll()}catch(e){status.textContent='Koneksi live gagal';const target=document.getElementById('viewer-actions');if(target)target.innerHTML='<div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">'+(e.message||'Cloudflare TURN belum dapat dihubungkan.')+'</div>'}}
 async function findLive(){if(isTarget||session||!canView)return;try{let data=await fetch('{{ url('monitoring/users') }}/'+targetId+'/active-session',{headers:{Accept:'application/json'}}).then(r=>r.json());if(!data.session)return;session=data.session.id;status.textContent='Menghubungkan live stream...';status.className='rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';document.getElementById('viewer-actions').innerHTML='<div class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600">Menghubungkan live monitoring...</div>';join()}catch(e){}}
 async function end(notify){clearInterval(poller);if(pc){pc.close();pc=null}if(isTarget&&session&&notify)try{await post(url('stop',session))}catch(e){};[screenStream,cameraStream,cameraPublishedStream].forEach(s=>s&&s.getTracks().forEach(t=>t.stop()));cancelAnimationFrame(cameraFrame);cameraSourceVideo=null;cameraCanvas=null;screenStream=null;cameraStream=null;cameraPublishedStream=null;session=null;if(isTarget){document.getElementById('start-live').classList.remove('hidden');document.getElementById('allow-camera').classList.add('hidden');document.getElementById('stop-live').classList.add('hidden');document.getElementById('toggle-camera-blur').classList.add('hidden');cameraBlurred=false;say('Monitoring dihentikan.')}status.textContent='Sesi berakhir';}
 document.getElementById('start-live')?.addEventListener('click',start);document.getElementById('allow-camera')?.addEventListener('click',allowCamera);document.getElementById('stop-live')?.addEventListener('click',()=>end(true));
 document.getElementById('toggle-camera-blur')?.addEventListener('click',toggleCameraBlur);
 window.addEventListener('beforeunload',event=>{
   if(session&&(isTarget||canView)){
     event.preventDefault();
     event.returnValue='Live monitoring masih aktif. Hentikan sesi sebelum menutup halaman.';
   }
 });
 if(isTarget&&session){status.textContent='Monitoring masih aktif';beginPoll()}
 if(canView&&session)join();
 if(canView&&!session)setInterval(findLive,2000);
})();
</script>
@endpush

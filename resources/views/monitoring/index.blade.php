@extends('layout.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 rounded-2xl bg-zinc-950 px-6 py-7 text-white">
        <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-zinc-400">View-only browser monitoring</p>
        <h1 class="mt-2 text-3xl font-semibold">Live Monitoring</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">Streaming layar dan kamera berlangsung langsung dari browser target. Tidak ada kontrol perangkat dan target harus menyetujui izin pada setiap sesi.</p>
    </div>
    @if($availableTargets->isNotEmpty())
        <section class="mb-6 rounded-2xl border border-zinc-200 bg-white p-5">
            <p class="text-xs font-bold uppercase tracking-[.16em] text-zinc-500">Akses pemantauan saya</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($availableTargets as $availableTarget)
                    <a href="{{ route('monitoring.index', ['user' => $availableTarget->id]) }}" class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold text-zinc-800 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white">{{ $availableTarget->name ?: $availableTarget->email }}</a>
                @endforeach
            </div>
        </section>
    @endif
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
                <p class="mt-1 leading-6">Pilih satu window kerja yang ingin dibagikan. Pemantau hanya dapat melihat, bukan mengendalikan perangkat Anda. <b>Jangan tutup atau refresh tab ini selama share layar berlangsung</b>, karena monitoring akan langsung berhenti.</p>
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

            <div id="video-grid" class="mt-6">
                <div class="relative aspect-video overflow-hidden rounded-2xl bg-zinc-950 shadow-sm ring-1 ring-zinc-900/10">
                    <div class="absolute left-4 top-4 z-10 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-[10px] font-bold tracking-[.14em] text-white backdrop-blur">SHARE SCREEN</div>
                    <video id="screen-video" autoplay playsinline muted class="hidden h-full w-full object-contain"></video>
                    <div id="screen-empty" class="grid h-full place-items-center bg-zinc-100 text-sm text-zinc-500">Preview kerja akan muncul di sini.</div>
                    <div class="absolute bottom-4 right-4 z-10 w-[31%] min-w-40 max-w-80 overflow-hidden rounded-xl border border-white/25 bg-zinc-900 shadow-2xl ring-1 ring-black/20 sm:bottom-5 sm:right-5">
                        <div class="flex items-center justify-between gap-2 bg-zinc-950/90 px-3 py-2"><p class="text-[10px] font-bold tracking-[.14em] text-white">KAMERA</p><button id="toggle-camera-blur" type="button" class="hidden rounded-md bg-white/15 px-2 py-1 text-[10px] font-semibold text-white hover:bg-white/25">Blur latar</button></div>
                        <video id="camera-video" autoplay playsinline muted class="hidden aspect-video w-full bg-zinc-950 object-cover"></video>
                        <div id="camera-empty" class="grid aspect-video place-items-center bg-zinc-800 px-3 text-center text-xs text-zinc-400">Kamera belum aktif.</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>

@endsection

@push('script')
@vite('resources/js/monitoring-firebase.js')
<script src="{{ asset('vendor-local/mediapipe/selfie_segmentation.js') }}"></script>
<script>
(() => {
 const csrf='{{ csrf_token() }}', isTarget={{ $canStart ? 'true':'false' }}, canView={{ $canView ? 'true':'false' }}, sessionId={{ $activeSession?->id ?? 'null' }}, targetId={{ $target->id }}, currentUserId={{ auth()->id() }}, firebaseConfig=@json($firebaseWebConfig);
 const screen=document.getElementById('screen-video'), camera=document.getElementById('camera-video'), notice=document.getElementById('notice'), status=document.getElementById('live-status');
 let session=sessionId, pc, targetPeers=new Map(), screenStream, cameraStream, cameraPublishedStream, cameraSourceVideo, cameraCanvas, cameraFrame, cameraBlurred=false, selfieSegmenter, segmentationBusy=false, lastSegmentationAt=0, poller, pendingCandidates=new Map(), pollDelay=250, iceServers=[], iceRequest, realtime, realtimePromise;
 const url=(name,id)=>({start:'{{ route('monitoring.sessions.start') }}',join:'{{ url('monitoring/sessions') }}/'+id+'/join',ice:'{{ url('monitoring/sessions') }}/'+id+'/ice-servers',signal:'{{ url('monitoring/sessions') }}/'+id+'/signal',signals:'{{ url('monitoring/sessions') }}/'+id+'/signals',firebaseToken:'{{ url('monitoring/sessions') }}/'+id+'/firebase-token',stop:'{{ url('monitoring/sessions') }}/'+id+'/stop'})[name];
 const mediapipeBase='{{ asset('vendor-local/mediapipe') }}/';
 const say=(text,bad=false)=>{if(!notice)return;notice.textContent=text;notice.className='mt-3 text-xs font-semibold '+(bad?'text-red-700':'text-emerald-700')};
 const show=(video,empty,stream)=>{video.srcObject=stream;video.classList.remove('hidden');document.getElementById(empty).classList.add('hidden')};
 const waitForVideo=video=>new Promise((resolve,reject)=>{video.onloadedmetadata=resolve;video.onerror=reject;setTimeout(resolve,3000)});
 const post=async(u,body={})=>{let r=await fetch(u,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(body)});if(!r.ok)throw new Error((await r.json().catch(()=>({}))).message||'Permintaan gagal.');return r.json()};
 const firebaseReady=()=>window.ERPMonitoringFirebase?Promise.resolve():new Promise(resolve=>window.addEventListener('erp-monitoring-firebase-ready',resolve,{once:true}));
 async function ensureRealtime(){
   if(realtime)return realtime;
   if(realtimePromise)return realtimePromise;
   if(!firebaseConfig.apiKey||!firebaseConfig.projectId||!firebaseConfig.appId)return null;
   realtimePromise=(async()=>{
     await firebaseReady();
     const auth=await post(url('firebaseToken',session));
     realtime=await window.ERPMonitoringFirebase.connect({
       config:{...firebaseConfig,__erpUserId:currentUserId},token:auth.token,sessionId:session,
       onSignal:signal=>processSignals([signal]),
     });
     return realtime;
   })().catch(error=>{realtimePromise=null;console.error('Firebase monitoring connection error:',error);throw error});
   return realtimePromise;
 }
 const send=async(type,payload,recipientId=null)=>{
   const live=await ensureRealtime();
   if(live)return live.send(type,payload,recipientId||targetId);
   return post(url('signal',session),{type,payload,...(recipientId?{recipient_user_id:recipientId}:{})});
 };
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
 function peer(target, viewerId=null){
   if(target&&targetPeers.has(viewerId))return targetPeers.get(viewerId);
   if(!target&&pc)return pc;
   const connection=new RTCPeerConnection({iceServers});
   if(target)targetPeers.set(viewerId,connection);else pc=connection;
   connection.onicecandidate=e=>{if(e.candidate)send('candidate',e.candidate.toJSON(),target?viewerId:null).catch(()=>{})};
   connection.onconnectionstatechange=()=>{
     if(connection.connectionState==='connected'){
       status.textContent='Live monitoring terhubung';
       status.className='rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
       // Target must stay responsive for every additional admin that joins.
       // The viewer can poll less frequently once its own stream is ready.
       pollDelay=isTarget?250:750;
       beginPoll();
     }
     if(['failed','disconnected'].includes(connection.connectionState)){
       status.textContent='Koneksi live gagal';
     }
   };
   if(!target){
     // The target publishes one work preview and one camera stream.
     pc.addTransceiver('video',{direction:'recvonly'});
     pc.addTransceiver('video',{direction:'recvonly'});
     connection.ontrack=e=>{
       const media=new MediaStream([e.track]);
       if(e.transceiver.mid==='0')show(screen,'screen-empty',media);
       else show(camera,'camera-empty',media);
     };
   }
   return connection;
 }
 function publishTrack(connection, track, stream){
   const sender=connection.getSenders().find(item=>item.track?.id===track.id);
   return sender||connection.addTrack(track,stream);
 }
 const peerKey=(target,viewerId)=>target?'viewer:'+viewerId:'admin';
 async function flushCandidates(connection,key){if(!connection?.remoteDescription)return;const candidates=pendingCandidates.get(key)||[];while(candidates.length){try{await connection.addIceCandidate(candidates.shift())}catch(e){}}pendingCandidates.delete(key)}
 function queueCandidate(key,candidate){const candidates=pendingCandidates.get(key)||[];candidates.push(candidate);pendingCandidates.set(key,candidates)}
 function publishMedia(connection){screenStream?.getVideoTracks().forEach(track=>publishTrack(connection,track,screenStream));const activeCameraStream=cameraBlurred&&cameraPublishedStream?cameraPublishedStream:cameraStream;activeCameraStream?.getVideoTracks().forEach(track=>publishTrack(connection,track,activeCameraStream))}
 async function processSignals(signals){
   for(const signal of signals){let p=signal.payload||{},viewerId=Number(signal.sender_user_id),targetConnection=isTarget?peer(true,viewerId):peer(false),key=peerKey(isTarget,viewerId);if(signal.type==='ready'&&isTarget){publishMedia(targetConnection);await negotiate(viewerId)}
   else if(signal.type==='offer'&&!isTarget){await targetConnection.setRemoteDescription(description(p));await flushCandidates(targetConnection,key);let answer=await targetConnection.createAnswer();await targetConnection.setLocalDescription(answer);await send('answer',targetConnection.localDescription)}
   else if(signal.type==='answer'&&isTarget){await targetConnection.setRemoteDescription(description(p));await flushCandidates(targetConnection,key)}
   else if(signal.type==='candidate'){if(!targetConnection.remoteDescription)queueCandidate(key,p);else{try{await targetConnection.addIceCandidate(p)}catch(e){}}}}
 }
 async function handleSignals(){
   if(!session)return; try{let response=await fetch(url('signals',session),{headers:{Accept:'application/json'}});if(!response.ok)throw new Error('Sinyal monitoring tidak dapat dimuat.');let data=await response.json();if(data.status!=='active')return end(false);await processSignals(data.signals)}catch(e){console.error('Monitoring WebRTC error:',e);if(isTarget)say('Koneksi live gagal: '+(e.message||'periksa koneksi browser'),true);else{status.textContent='Koneksi live gagal';const target=document.getElementById('viewer-actions');if(target)target.innerHTML='<div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Koneksi live gagal. '+(e.message||'Silakan mulai ulang share layar target.')+'</div>'}}}
 async function beginPoll(){
   clearInterval(poller);
   try{if(await ensureRealtime())return}catch(error){console.warn('Firebase tidak dapat digunakan, memakai jalur cadangan.',error)}
   poller=setInterval(handleSignals,pollDelay);handleSignals();
 }
 async function negotiate(viewerId){const c=peer(true,viewerId);if(c.signalingState!=='stable')throw new Error('Menunggu koneksi sebelumnya selesai.');const offer=await c.createOffer();await c.setLocalDescription(offer);await send('offer',c.localDescription,viewerId)}
 const isVirtualCamera=device=>/(obs|virtual|manycam|snap camera|xsplit|ndi|droidcam|iriun|epoccam)/i.test(String(device?.label||''));
 async function hasPhysicalCamera(){
   if(!navigator.mediaDevices?.getUserMedia)return false;
   if(!navigator.mediaDevices.enumerateDevices)return true;
   try{
     const cameras=(await navigator.mediaDevices.enumerateDevices()).filter(device=>device.kind==='videoinput');
     if(!cameras.length)return false;
     const labelled=cameras.filter(device=>String(device.label||'').trim()!=='');
     return labelled.length?labelled.some(device=>!isVirtualCamera(device)):true;
   }catch(e){return true}
 }
 async function start(){
   if(screenStream)return;
   if(!window.isSecureContext||!navigator.mediaDevices?.getDisplayMedia)return say('Browser hanya dapat membagikan window melalui HTTPS atau localhost. Buka ERP memakai https://, bukan http://alamat-IP.',true);
   try{
     screenStream=await navigator.mediaDevices.getDisplayMedia({video:{frameRate:{ideal:10,max:15},width:{ideal:1280,max:1280},height:{ideal:720,max:720},displaySurface:'window'},audio:false,preferCurrentTab:false,selfBrowserSurface:'exclude',surfaceSwitching:'include'});
     show(screen,'screen-empty',screenStream);screenStream.getVideoTracks()[0].onended=()=>end(true);document.getElementById('start-live').classList.add('hidden');
     const hasCamera=await hasPhysicalCamera();
     if(!hasCamera){document.getElementById('camera-empty').textContent='Kamera tidak terdeteksi. Live berjalan dengan window kerja saja.';await activateLive(false);return}
     document.getElementById('allow-camera').classList.remove('hidden');say('Window kerja sudah diizinkan. Lanjutkan dengan izin kamera.');
   }catch(e){say('Izin window tidak diberikan. Pilih window Chrome lalu klik Izinkan pada dialog browser.',true)}
 }
 async function setupBackgroundBlur(){
   if(selfieSegmenter)return;
   if(!window.SelfieSegmentation)throw new Error('Mesin blur latar belum tersedia.');
   selfieSegmenter=new SelfieSegmentation({locateFile:file=>mediapipeBase+file});
   selfieSegmenter.setOptions({modelSelection:1});
   selfieSegmenter.onResults(results=>{
     if(!cameraCanvas)return;
     const context=cameraCanvas.getContext('2d'),width=cameraCanvas.width,height=cameraCanvas.height;
     context.save();context.clearRect(0,0,width,height);
     context.drawImage(results.segmentationMask,0,0,width,height);
     context.globalCompositeOperation='source-in';context.drawImage(results.image,0,0,width,height);
     context.globalCompositeOperation='destination-over';context.filter='blur(14px)';context.drawImage(results.image,0,0,width,height);
     context.restore();segmentationBusy=false;
   });
 }
 function renderCamera(){
   if(!cameraCanvas||!cameraSourceVideo||!cameraBlurred)return;
   const now=performance.now();
   if(!segmentationBusy&&cameraSourceVideo.readyState>=2&&now-lastSegmentationAt>66){
     segmentationBusy=true;lastSegmentationAt=now;
     selfieSegmenter.send({image:cameraSourceVideo}).catch(()=>{segmentationBusy=false});
   }
   cameraFrame=requestAnimationFrame(renderCamera);
 }
 async function toggleCameraBlur(){
   if(!cameraStream)return;
   const button=document.getElementById('toggle-camera-blur');
   button.disabled=true;
   try{
     const cameraSenders=Array.from(targetPeers.values()).map(connection=>connection.getSenders().find(sender=>sender.track?.id===cameraStream.getVideoTracks()[0]?.id||sender.track?.id===cameraPublishedStream?.getVideoTracks()[0]?.id)).filter(Boolean);
     if(!cameraBlurred){
       cameraSourceVideo=document.createElement('video');cameraSourceVideo.muted=true;cameraSourceVideo.playsInline=true;cameraSourceVideo.srcObject=cameraStream;
       cameraSourceVideo.style.cssText='position:fixed;left:-9999px;top:-9999px;width:1px;height:1px;opacity:0;pointer-events:none';document.body.appendChild(cameraSourceVideo);
       await cameraSourceVideo.play();await waitForVideo(cameraSourceVideo);
       await setupBackgroundBlur();cameraCanvas=document.createElement('canvas');cameraCanvas.width=cameraSourceVideo.videoWidth||640;cameraCanvas.height=cameraSourceVideo.videoHeight||360;cameraBlurred=true;renderCamera();cameraPublishedStream=cameraCanvas.captureStream(15);
       await Promise.all(cameraSenders.map(sender=>sender.replaceTrack(cameraPublishedStream.getVideoTracks()[0])));show(camera,'camera-empty',cameraPublishedStream);cameraBlurred=true;button.textContent='Nonaktifkan blur';say('Blur kamera aktif. Tampilan admin juga dibuat blur.');
     }else{
       await Promise.all(cameraSenders.map(sender=>sender.replaceTrack(cameraStream.getVideoTracks()[0])));show(camera,'camera-empty',cameraStream);cameraPublishedStream?.getTracks().forEach(track=>track.stop());cancelAnimationFrame(cameraFrame);cameraSourceVideo?.remove();cameraSourceVideo=null;cameraCanvas=null;cameraPublishedStream=null;cameraBlurred=false;button.textContent='Aktifkan blur';say('Blur kamera dinonaktifkan.');
     }
   }catch(e){say('Blur kamera tidak dapat diubah: '+(e.message||'coba ulangi monitoring.'),true)}finally{button.disabled=false}
 }
 async function allowCamera(){
   const allowButton=document.getElementById('allow-camera');
   if(!screenStream||allowButton?.disabled)return;
   if(allowButton)allowButton.disabled=true;
   try{cameraStream=await navigator.mediaDevices.getUserMedia({video:{frameRate:{ideal:10,max:15},width:{ideal:640,max:640},height:{ideal:360,max:360}},audio:false});const cameraTrack=cameraStream.getVideoTracks()[0];if(isVirtualCamera({label:cameraTrack?.label})){cameraStream.getTracks().forEach(track=>track.stop());cameraStream=null;allowButton.classList.add('hidden');document.getElementById('camera-empty').textContent='Kamera fisik tidak terdeteksi. Kamera virtual diabaikan.';await activateLive(false);return}show(camera,'camera-empty',cameraStream);await activateLive(true)}catch(e){if(['NotFoundError','DevicesNotFoundError'].includes(e?.name)){allowButton.classList.add('hidden');document.getElementById('camera-empty').textContent='Kamera tidak terdeteksi. Live berjalan dengan window kerja saja.';await activateLive(false);return}if(allowButton)allowButton.disabled=false;say('Live monitoring tidak dapat dimulai: '+(e.message||'periksa izin kamera dan konfigurasi TURN.'),true)}}
 async function activateLive(withCamera){
   let data=await post(url('start'),{consent:true,camera_available:withCamera});session=data.session.id;await loadIceServers();document.getElementById('allow-camera').classList.add('hidden');document.getElementById('stop-live').classList.remove('hidden');document.getElementById('toggle-camera-blur').classList.toggle('hidden',!withCamera);status.textContent='Live monitoring aktif';status.className='rounded-full bg-red-50 px-3 py-2 text-xs font-semibold text-red-700';say(withCamera?'Streaming layar dan kamera aktif. Menunggu admin terhubung.':'Streaming window kerja aktif tanpa kamera. Menunggu admin terhubung.');beginPoll()
 }
 async function join(){try{await Promise.all([post(url('join',session)),loadIceServers()]);peer(false);const live=await ensureRealtime();if(live)await live.send('ready',{},targetId);status.textContent='Menghubungkan live stream...';beginPoll()}catch(e){status.textContent='Koneksi live gagal';const target=document.getElementById('viewer-actions');if(target)target.innerHTML='<div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">'+(e.message||'Cloudflare TURN belum dapat dihubungkan.')+'</div>'}}
 async function findLive(){if(isTarget||session||!canView)return;try{let data=await fetch('{{ url('monitoring/users') }}/'+targetId+'/active-session',{headers:{Accept:'application/json'}}).then(r=>r.json());if(!data.session)return;session=data.session.id;status.textContent='Menghubungkan live stream...';status.className='rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';document.getElementById('viewer-actions').innerHTML='<div class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600">Menghubungkan live monitoring...</div>';join()}catch(e){}}
 async function end(notify){clearInterval(poller);realtime?.disconnect();realtime=null;realtimePromise=null;if(pc){pc.close();pc=null}targetPeers.forEach(connection=>connection.close());targetPeers.clear();pendingCandidates.clear();if(isTarget&&session&&notify)try{await post(url('stop',session))}catch(e){};[screenStream,cameraStream,cameraPublishedStream].forEach(s=>s&&s.getTracks().forEach(t=>t.stop()));cancelAnimationFrame(cameraFrame);cameraSourceVideo?.remove();cameraSourceVideo=null;cameraCanvas=null;screenStream=null;cameraStream=null;cameraPublishedStream=null;session=null;if(isTarget){document.getElementById('start-live').classList.remove('hidden');document.getElementById('allow-camera').classList.add('hidden');document.getElementById('stop-live').classList.add('hidden');document.getElementById('toggle-camera-blur').classList.add('hidden');cameraBlurred=false;say('Monitoring dihentikan.')}status.textContent='Sesi berakhir';}
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
 if(canView&&!session)setInterval(findLive,750);
})();
</script>
@endpush

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kepegawaian</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
<header>
    <h1>Sistem Informasi Kepegawaian</h1>
    
    <div style="display: flex; align-items: center; gap: 15px;">
        <button id="theme-toggle" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); color: white; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
            🌙 Mode Gelap
        </button>

        <div class="user-info">
            Halo, <?= htmlspecialchars(Session::get('nama') ?? 'User') ?>
            (<?= Session::get('role') ?? '' ?>)
        </div>
    </div>
</header>

<?php include __DIR__ . '/sidebar.php'; ?>
<main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('theme-toggle');
    const htmlTag = document.documentElement;
    
    if (!toggleBtn) {
        console.error("Tombol dengan id 'theme-toggle' tidak ditemukan!");
        return;
    }
    if (htmlTag.classList.contains('dark-mode')) {
        toggleBtn.innerHTML = '☀️ Mode Terang';
    } else {
        toggleBtn.innerHTML = '🌙 Mode Gelap';
    }

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault(); 
        
     
        htmlTag.classList.toggle('dark-mode');
        
    
        if (htmlTag.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            toggleBtn.innerHTML = '☀️ Mode Terang';
            console.log("Tema berhasil diubah ke: DARK");
        } else {
            localStorage.setItem('theme', 'light');
            toggleBtn.innerHTML = '🌙 Mode Gelap';
            console.log("Tema berhasil diubah ke: LIGHT");
        }
    });
});
</script>
<div class="music-card" style="
    background: var(--bg-card); 
    border: 1px solid var(--border-color); 
    padding: 8px 15px; 
    border-radius: 12px; 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
">
    <audio id="bg-audio">
        <source src="" type="audio/mpeg">
    </audio>
    
    <div style="display: flex; align-items: center; gap: 8px; border-right: 1px solid var(--border-color); padding-right: 10px;">
        <img id="music-disk" src="assets/audio/img/default.png" alt="Disk" 
             style="width: 24px; 
                    height: 24px; 
                    border-radius: 50%; 
                    object-fit: cover; 
                    transition: transform 2s linear; 
                    display: block;">
        
        <div style="display: flex; flex-direction: column; position: relative;">
            <button id="music-toggle" style="background: transparent; border: none; color: var(--text-main); cursor: pointer; font-size: 13px; font-weight: bold; padding: 0; text-align: left; outline: none;">
                <span id="music-status">Musik: Mati</span>
            </button>
            
            <div style="display: flex; align-items: center; gap: 5px;">
                <small id="track-name" style="font-size: 10px; color: var(--text-muted); width: 75px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Klik Play</small>
                
                <div id="wave-container" class="audio-wave">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </div>
    
    <button id="music-next" title="Putar Lagu Acak" style="background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); cursor: pointer; font-size: 12px; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
        ⏭️
    </button>

    <div style="display: flex; align-items: center; gap: 8px; background: var(--input-bg); padding: 4px 10px; border-radius: 20px; border: 1px solid var(--input-border);">
        <span id="volume-icon" style="font-size: 12px; cursor: pointer;" title="Klik untuk Bisukan">🔊</span>
        <input type="range" id="music-volume" min="0" max="1" step="0.05" value="0.25" 
               style="width: 60px !important; max-width: 60px !important; height: 4px !important; cursor: pointer; accent-color: #3498db; margin: 0 !important; padding: 0 !important; box-sizing: content-box !important;">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('bg-audio');
    const musicBtn = document.getElementById('music-toggle');
    const nextBtn = document.getElementById('music-next');
    const musicStatus = document.getElementById('music-status');
    const volumeSlider = document.getElementById('music-volume');
    const volumeIcon = document.getElementById('volume-icon');
    const trackName = document.getElementById('track-name');
    const diskIcon = document.getElementById('music-disk');
    const waveContainer = document.getElementById('wave-container');
    
    // 1. DAFTAR LAGU, COVER DI FOLDER IMG, DAN JUDUL YANG BERPASANGAN
    const playlist = [
        {
            file: 'assets/audio/song/lagu1.mp3',
            cover: 'assets/audio/img/cover1.png', // Sesuaikan nama file gambar Anda
            title: 'Ambatukam Termuwani'
        },
        {
            file: 'assets/audio/song/lagu2.mp3',
            cover: 'assets/audio/img/cover2.png', // Bisa menggunakan .jpg
            title: 'Angelic Tung Tung'
        },
        {
            file: 'assets/audio/song/lagu3.mp3',
            cover: 'assets/audio/img/cover3.png',
            title: 'NCS'
        }
    ];
    
    let lastVolume = 0.10;

    function toggleAnimation(play) {
        if(play) {
            diskIcon.style.animation = "spin 3s linear infinite";
            waveContainer.classList.add('playing');
        } else {
            diskIcon.style.animation = "none";
            waveContainer.classList.remove('playing');
        }
    }

    function updateVolumeIcon(vol) {
        if (vol == 0) volumeIcon.textContent = '🔇';
        else if (vol < 0.4) volumeIcon.textContent = '🔈';
        else if (vol < 0.7) volumeIcon.textContent = '🔉';
        else volumeIcon.textContent = '🔊';
    }

    // Fungsi memperbarui tampilan data lagu dan gambar cover
    function setTrackData(trackObj) {
        if (!trackObj) return;
        trackName.textContent = trackObj.title;
        diskIcon.src = trackObj.cover;
    }

    // Fungsi mengambil lagu acak yang tidak sama dengan lagu yang sedang berjalan
    function getRandomTrack() {
        if (playlist.length <= 1) return playlist[0];
        let newTrack;
        const currentSrc = audio.getAttribute('src') || '';
        do {
            newTrack = playlist[Math.floor(Math.random() * playlist.length)];
        } while (currentSrc.includes(newTrack.file));
        return newTrack;
    }

    function playNewRandomSong() {
        const track = getRandomTrack();
        audio.src = track.file;
        audio.load();
        setTrackData(track);
        
        if (localStorage.getItem('music_playing') === 'true') {
            audio.play().catch(err => console.log("Autoplay ditunda"));
            musicStatus.textContent = 'Musik: Menyala';
            toggleAnimation(true);
        }
    }

    // Inisialisasi Volume
    const savedVolume = localStorage.getItem('music_volume');
    audio.volume = savedVolume !== null ? parseFloat(savedVolume) : 0.25;
    volumeSlider.value = audio.volume;
    updateVolumeIcon(audio.volume);

    // Load State Terakhir (Menjaga agar musik mulus saat pindah halaman)
    if (localStorage.getItem('music_playing') === 'true') {
        const savedSongFile = localStorage.getItem('current_song');
        const savedTime = localStorage.getItem('current_time');
        
        // Cari objek track di playlist berdasarkan file mp3 yang tersimpan
        const currentTrack = playlist.find(t => t.file === savedSongFile) || playlist[0];
        
        audio.src = currentTrack.file;
        audio.load();
        if (savedTime) audio.currentTime = parseFloat(savedTime);
        setTrackData(currentTrack);
        
        audio.play().then(() => {
            musicStatus.textContent = 'Musik: Menyala';
            toggleAnimation(true);
        }).catch(() => {
            musicStatus.textContent = 'Musik: Tertunda';
            const startOnInteraction = () => {
                if (localStorage.getItem('music_playing') === 'true' && audio.paused) {
                    audio.play().then(() => { 
                        musicStatus.textContent = 'Musik: Menyala'; 
                        toggleAnimation(true);
                    });
                }
                document.removeEventListener('click', startOnInteraction);
            };
            document.addEventListener('click', startOnInteraction);
        });
    } else {
        const initialTrack = playlist[0];
        audio.src = initialTrack.file;
        setTrackData(initialTrack);
    }

    // Event Listener Slider Volume
    volumeSlider.addEventListener('input', function() {
        audio.volume = this.value;
        localStorage.setItem('music_volume', this.value);
        updateVolumeIcon(this.value);
        if (this.value > 0) lastVolume = this.value;
    });

    // Event Listener Klik Mute / Unmute
    volumeIcon.addEventListener('click', function() {
        if (audio.volume > 0) {
            lastVolume = audio.volume;
            audio.volume = 0;
            volumeSlider.value = 0;
        } else {
            audio.volume = lastVolume;
            volumeSlider.value = lastVolume;
        }
        localStorage.setItem('music_volume', audio.volume);
        updateVolumeIcon(audio.volume);
    });

    // Play / Pause
    musicBtn.addEventListener('click', function() {
        if (audio.paused) {
            audio.play();
            localStorage.setItem('music_playing', 'true');
            const currentTrack = playlist.find(t => audio.src.includes(t.file)) || playlist[0];
            localStorage.setItem('current_song', currentTrack.file);
            musicStatus.textContent = 'Musik: Menyala';
            toggleAnimation(true);
        } else {
            audio.pause();
            localStorage.setItem('music_playing', 'false');
            musicStatus.textContent = 'Musik: Mati';
            toggleAnimation(false);
        }
    });

    nextBtn.addEventListener('click', function() {
        localStorage.setItem('music_playing', 'true');
        playNewRandomSong();
    });

    audio.addEventListener('ended', playNewRandomSong);

    window.addEventListener('beforeunload', function() {
        if (!audio.paused) {
            localStorage.setItem('current_time', audio.currentTime);
            const currentTrack = playlist.find(t => audio.src.includes(t.file));
            if(currentTrack) localStorage.setItem('current_song', currentTrack.file);
        }
    });
});
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
#music-next:hover {
    transform: scale(1.1);
    background: var(--border-color);
}

/* Style Animasi Wave Audio */
.audio-wave {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    width: 15px;
    height: 10px;
}
.audio-wave span {
    display: block;
    width: 2px;
    height: 2px;
    background: #3498db;
    border-radius: 1px;
    transition: all 0.2s ease;
}
/* Jalankan animasi hanya saat musik diset "playing" */
.audio-wave.playing span:nth-child(1) { animation: bounce 0.6s ease infinite alternate; }
.audio-wave.playing span:nth-child(2) { animation: bounce 0.4s ease infinite alternate 0.2s; }
.audio-wave.playing span:nth-child(3) { animation: bounce 0.5s ease infinite alternate 0.1s; }

@keyframes bounce {
    from { height: 2px; }
    to { height: 10px; }
}
</style>
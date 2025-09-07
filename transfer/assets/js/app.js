(() => {
  const uploadUrl = window.__APP__?.uploadUrl || 'upload.php';
  const form = document.getElementById('uploadForm');
  const fileInput = document.getElementById('fileInput');
  const dropzone = document.getElementById('dropzone');
  const recipientInput = document.getElementById('recipient');
  const sendBtn = document.getElementById('sendBtn');
  const progress = document.getElementById('progress');
  const progressBar = document.getElementById('progressBar');
  const progressLabel = document.getElementById('progressLabel');
  const result = document.getElementById('result');
  const downloadLink = document.getElementById('downloadLink');
  const copyBtn = document.getElementById('copyBtn');

  const setProgress = (p) => {
    const clamped = Math.max(0, Math.min(100, Math.round(p)));
    progressBar.style.width = clamped + '%';
    progressLabel.textContent = clamped + '%';
  };

  const resetUI = () => {
    progress.hidden = true;
    result.hidden = true;
    setProgress(0);
  };

  dropzone.addEventListener('click', () => fileInput.click());
  dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
  });
  dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
  });
  dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
      fileInput.files = files;
    }
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    resetUI();

    if (!fileInput.files || fileInput.files.length === 0) {
      alert('Veuillez choisir un fichier.');
      return;
    }

    const file = fileInput.files[0];
    const recipient = recipientInput.value.trim();

    const data = new FormData();
    data.append('file', file);
    if (recipient) data.append('recipient', recipient);

    sendBtn.disabled = true;
    progress.hidden = false;
    setProgress(0);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl, true);
    xhr.upload.onprogress = (ev) => {
      if (ev.lengthComputable) {
        setProgress((ev.loaded / ev.total) * 100);
      }
    };
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        sendBtn.disabled = false;
        try {
          const res = JSON.parse(xhr.responseText || '{}');
          if (xhr.status >= 200 && xhr.status < 300 && res.ok) {
            const link = res.pretty_url || res.download_url;
            downloadLink.value = link;
            result.hidden = false;
            setProgress(100);
          } else {
            alert(res.error || 'Erreur lors de l\'upload');
          }
        } catch (err) {
          alert('Erreur réseau ou serveur.');
        }
      }
    };
    xhr.send(data);
  });

  copyBtn.addEventListener('click', async () => {
    const value = downloadLink.value;
    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(value);
      } else {
        downloadLink.select();
        document.execCommand('copy');
      }
      copyBtn.textContent = 'Copié !';
      setTimeout(() => (copyBtn.textContent = 'Copier'), 1500);
    } catch (e) {
      alert('Impossible de copier automatiquement. Copiez manuellement.');
    }
  });
})();



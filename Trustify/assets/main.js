function encryptText() {
    const text = document.getElementById('textToEncrypt').value;

    fetch('backend/services.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ service: 'encrypt', text: text })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('encryptionResult').innerText = `Error: ${data.error}`;
        } else {
            document.getElementById('encryptionResult').innerText = `Encrypted Text: ${data.encryptedText}`;
        }
    })
    .catch(error => console.error('Error:', error));
}
function hashText() {
    const text = document.getElementById('textToHash').value;

    fetch('backend/services.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ service: 'hash', text: text })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('hashingResult').innerText = `Error: ${data.error}`;
        } else {
            document.getElementById('hashingResult').innerText = `Hash: ${data.hash}`;
        }
    })
    .catch(error => console.error('Error:', error));
}
function signCertificate() {
    const details = document.getElementById('certDetails').value;

    fetch('backend/services.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ service: 'sign', details: details })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('signingResult').innerText = `Error: ${data.error}`;
        } else {
            document.getElementById('signingResult').innerText = `Signed Certificate: ${data.signedCert}`;
        }
    })
    .catch(error => console.error('Error:', error));
}
function decryptText() {
    const encryptedText = document.getElementById('encryptedText').value;
    const iv = document.getElementById('decryptionIv').value;

    fetch('backend/services.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ service: 'decrypt', encryptedText: encryptedText, iv: iv })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('decryptionResult').innerText = `Error: ${data.error}`;
        } else {
            document.getElementById('decryptionResult').innerText = `Decrypted Text: ${data.decryptedText}`;
        }
    })
    .catch(error => console.error('Error:', error));
}

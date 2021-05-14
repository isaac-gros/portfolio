/**
 * File explorer for uploads
 */
const explorer = document.getElementById('explorer');
const explorerDone = document.getElementById('explorer-done');
const explorerForm = document.getElementById('explorer-selection');
const explorerImport = document.getElementById('uploads_uploads');
const filesList = document.getElementById('file-list');
const explorerButtons = document.querySelectorAll('.file-explorer');

// Determine if the explorer has been loaded once
let explorerLoaded = false;

// Init explorer for a button
explorerButtons.forEach((explorerButton) => {
    explorerButton.addEventListener('click', (event) => {

        // Retrieve target input details
        let isMultiple = (event.target.dataset.multiple == "true");
        let inputTargetName = event.target.dataset.input;
        let inputTargetValue = document.querySelector(`[name="${inputTargetName}"]`).value;

        initExplorer(explorerLoaded).then(isLoaded => {
            explorerLoaded = isLoaded; // Prevent explorer to load uploads twice (or more !)
            if(explorerLoaded) {
                displaySelectionButtons(isMultiple, inputTargetValue);
                explorerForm.dataset.target = event.target.dataset.input;
            }
        });
    });
});

// Update hidden inputs values when done
explorerDone.addEventListener('click', () => {
    let targetName = explorerForm.dataset.target;
    let inputTarget = document.querySelector(`[name="${targetName}"]`);

    let formData = [];
    let formInput = explorerForm.querySelectorAll('input');
    formInput.forEach(input => {
        if(input.type == 'radio') {
            formData = (input.checked) ? input.value : formData;
        } else {
            formData = (input.checked) ? [...formData, input.value] : formData;
        }
    });

    inputTarget.value = formData;
});

// Send uploaded to the server file and display it
explorerImport.addEventListener('change', () => {

    // Create form data
    let formData = new FormData();

    // Add files to form data
    formData.append('uploads', explorerImport.files[0]);

    // Add token to form data
    let token = document.getElementById('uploads__token');
    formData.append('_token', token.value);

    fetch('/add/upload', {
        method: 'POST',
        body: formData
    }).then(response => {
        // 
    });
}, false);

/**
 * Initialize the explorer for the first time
 * @param {boolean} isLoaded
 * @returns 
 */
async function initExplorer(isLoaded) {
    return new Promise((resolve, reject) => {
        if (!isLoaded) {
            fetchUploads().then(response => {
                if(response.status == 200) {
                    displayUploads(response.data);
                    resolve(true);
                } else {
                    reject(false);
                }
            });
        } else {
            resolve(true);
        }
    });
}

/**
 * Fetch uploads from the server
 */
async function fetchUploads() {
    return new Promise((resolve, reject) => {
        fetch('/api/uploads')
            .then(response => {
                if (typeof (response) != 'undefined') {
                    response.json().then(function (json) {
                        resolve({
                            status: 200,
                            data: json
                        });
                    });
                } else {
                    resolve({
                        status: 500,
                        message: "Une erreur est survenue."
                    });
                }
            }).catch(error => {
                reject({
                    status: 500,
                    message: "Une erreur est survenue. " + error
                });
            });
    });
}

/**
 * Display the files
 * @param {Array} uploads
 * @return {void}
 */
function displayUploads(uploads) {
    uploads.forEach(upload => {

        // Create child elements
        let thumbnailContainer = document.createElement('li');
        let thumbnail = document.createElement('img');

        // Set data
        thumbnailContainer.classList = ['file-list__element'];
        thumbnailContainer.dataset.value = upload.id;
        thumbnail.src = upload.url;
        
        // Add click event listener for image to trigger input
        thumbnail.addEventListener('click', (event) => {
            let thumbnailInput = event.target.previousElementSibling
            thumbnailInput.checked = !thumbnailInput.checked; 
        });

        // Append elements
        thumbnailContainer.append(thumbnail);
        filesList.append(thumbnailContainer);
    });
}

/**
 * Display selection button for target input
 * @param {boolean} isMultiple
 * @param {String|Array|null} inputValue
 * @return {void} 
 */
function displaySelectionButtons(isMultiple, inputValue = null) {
    
    // Remove the old input if any
    let previousInputs = document.querySelectorAll('#file-list input');
    previousInputs.forEach(input => { input.remove() });

    // Get the previously selected data
    let selectedData = [];
    if(inputValue && typeof(inputValue) == 'string') {
        selectedData = inputValue.split(',');
    } else {
        selectedData = inputValue;
    }

    // Set new inputs
    let thumbnails = document.querySelectorAll('#file-list li');
    thumbnails.forEach((thumbnail, key) => {
        let selectValue = thumbnail.dataset.value;
        let selectButton = document.createElement('input');
        
        selectButton.type = (isMultiple) ? 'checkbox' : 'radio';
        selectButton.name = (isMultiple) ? key : 'selection';
        selectButton.classList = ['file-list__element__button'];
        selectButton.form = 'explorer-selection';
        selectButton.value = selectValue;

        if(selectedData != null) {
            if(selectedData.length == 1) {
                selectButton.checked = (selectedData[0] == selectValue);
            } else {
                selectButton.checked = (selectedData[key] == selectValue);
            }
        }

        thumbnail.prepend(selectButton);
    });
}

/**
 * Handle file upload 
 * and send it to the server
 */
function importUpload() {
    console.log('OK');
}
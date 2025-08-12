document.addEventListener('DOMContentLoaded', function() {
    let currentStudentId = null;
    let currentSolicitudId = null;
    let documentosRequeridos = [];
    let documentosSubidos = [];
    
    // Verificamos que existan los modales antes de crearlos
    const uploadModalElement = document.getElementById('uploadModal');
    const viewDocumentModalElement = document.getElementById('viewDocumentModal');
    const uploadModal = uploadModalElement ? new bootstrap.Modal(uploadModalElement) : null;
    const viewDocumentModal = viewDocumentModalElement ? new bootstrap.Modal(viewDocumentModalElement) : null;
    
    initApp();
    
    function initApp() {
        loadStudentInfo();
        setupEventListeners();
    }
    
    function setupEventListeners() {
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('documentFile');
        const uploadBtn = document.getElementById('btnUploadDocument');
        const refreshBtn = document.getElementById('btnRefreshDocuments'); // Obtenemos el nuevo botón

        // --- EVENTO PARA EL NUEVO BOTÓN DE ACTUALIZAR ---
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                // Usamos la función de alerta existente en esta página
                showAlert('Actualizando lista de documentos...', 'info');
                // Volvemos a llamar a la función que carga los documentos
                loadStudentDocuments();
            });
        }
        
        if (dropArea && fileInput) {
            dropArea.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', handleFileSelect);
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            dropArea.addEventListener('drop', handleDrop, false);
        }
        
        if (uploadBtn) {
            uploadBtn.addEventListener('click', uploadDocument);
        }
    }
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        const dropArea = document.getElementById('dropArea');
        if (dropArea) {
            dropArea.classList.add('bg-primary', 'bg-opacity-10');
        }
    }
    
    function unhighlight() {
        const dropArea = document.getElementById('dropArea');
        if (dropArea) {
            dropArea.classList.remove('bg-primary', 'bg-opacity-10');
        }
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    function handleFileSelect(e) {
        const files = e.target.files;
        handleFiles(files);
    }
    
    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            const fileInfo = document.getElementById('fileInfo');
            
            if (!fileInfo) return;
            
            const validTypes = ['application/pdf', 'application/msword', 
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg', 'image/png'];
            
            if (!validTypes.includes(file.type)) {
                showAlert('Solo se permiten archivos PDF, Word o imágenes (JPG/PNG)', 'danger');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // Límite de 5MB
                showAlert('El archivo no debe exceder los 5MB', 'danger');
                return;
            }
            
            fileInfo.innerHTML = `
                <strong>${file.name}</strong><br>
                <small>Tipo: ${file.type}</small><br>
                <small>Tamaño: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
            `;
        }
    }

    function loadStudentInfo() {
        fetch('../php/estudiantephp/documentos.php?action=getStudentInfo', {
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                currentStudentId = data.estudiante_id;
                currentSolicitudId = data.solicitud_id;
                const nombreUsuario = document.getElementById('nombreUsuarioText');
                if (nombreUsuario) {
                    nombreUsuario.textContent = `${data.nombre} ${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim();
                }
                loadStudentDocuments();
            } else {
                showAlert(data.message || 'Error al cargar información del estudiante', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al conectar con el servidor', 'danger');
        });
    }
    
    function loadStudentDocuments() {
        const loadingElement = document.getElementById('loadingDocuments');
        const documentosContainer = document.getElementById('documentosContainer');
        
        if (!currentSolicitudId) {
            showAlert('No tienes una solicitud de servicio social activa', 'warning');
            if (loadingElement) loadingElement.style.display = 'none';
            documentosContainer.innerHTML = `<div class="col-12 text-center py-4"><p>No se encontró una solicitud activa.</p></div>`;
            return;
        }
        
        // Muestra el spinner de carga al iniciar la recarga
        if (loadingElement) loadingElement.style.display = 'block';
        if (documentosContainer) documentosContainer.innerHTML = '';


        fetch(`../php/estudiantephp/documentos.php?action=getStudentDocuments&solicitud_id=${currentSolicitudId}`, {
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                documentosRequeridos = data.documentos_requeridos.filter(doc => doc.tipo_documento_id != 4);
                documentosSubidos = data.documentos_subidos.filter(doc => doc.tipo_documento_id != 4);
                renderDocuments();
            } else {
                showAlert(data.message || 'Error al cargar documentos', 'danger');
            }
            if (loadingElement) loadingElement.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al conectar con el servidor', 'danger');
            if (loadingElement) loadingElement.style.display = 'none';
        });
    }
    
    function renderDocuments() {
        const documentosContainer = document.getElementById('documentosContainer');
        if (!documentosContainer) return;
        
        documentosContainer.innerHTML = '';
        
        if (documentosRequeridos.length === 0) {
            documentosContainer.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="bi bi-file-earmark-excel fs-1 text-muted"></i>
                    <p class="mt-2">No hay documentos requeridos para mostrar</p>
                </div>
            `;
            return;
        }
        
        documentosRequeridos.forEach(doc => {
            const documentoSubido = documentosSubidos.find(d => d.tipo_documento_id == doc.tipo_documento_id);
            
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4 mb-4';
            card.innerHTML = `
                <div class="card document-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">${doc.nombre}</h5>
                            <span class="document-status">
                                ${getStatusBadge(documentoSubido?.estado || 'pendiente')}
                            </span>
                        </div>
                        <p class="card-text text-muted small">${doc.descripcion || 'Sin descripción'}</p>
                        
                        <div class="document-actions mt-auto pt-3">
                            ${documentoSubido ? `
                                <button class="btn btn-sm btn-outline-primary me-2 view-document" 
                                        data-document-id="${documentoSubido.documento_id}">
                                    <i class="bi bi-eye me-1"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-outline-secondary upload-document" 
                                        data-doc-type="${doc.tipo_documento_id}">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reemplazar
                                </button>
                            ` : `
                                <button class="btn btn-sm btn-primary upload-document" 
                                        data-doc-type="${doc.tipo_documento_id}">
                                    <i class="bi bi-upload me-1"></i> Subir
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            `;
            
            documentosContainer.appendChild(card);
        });
        
        document.querySelectorAll('.upload-document').forEach(btn => {
            btn.addEventListener('click', function() {
                openUploadModal(this.getAttribute('data-doc-type'));
            });
        });
        
        document.querySelectorAll('.view-document').forEach(btn => {
            btn.addEventListener('click', function() {
                viewDocument(this.getAttribute('data-document-id'));
            });
        });
    }
    
    function getStatusBadge(status) {
        switch(status) {
            case 'aprobada': return '<span class="badge bg-success">Aprobado</span>';
            case 'rechazada': return '<span class="badge bg-danger">Rechazado</span>';
            case 'pendiente': return '<span class="badge bg-warning">Pendiente</span>';
            default: return '<span class="badge bg-secondary">Sin subir</span>';
        }
    }
    
    function openUploadModal(tipoDocumentoId) {
        document.getElementById('documentTypeId').value = tipoDocumentoId;
        document.getElementById('fileInfo').textContent = 'Ningún archivo seleccionado';
        document.getElementById('documentObservations').value = '';
        document.getElementById('documentFile').value = '';
        uploadModal.show();
    }
    
    function uploadDocument() {
        const tipoDocumentoId = document.getElementById('documentTypeId').value;
        const fileInput = document.getElementById('documentFile');
        const observaciones = document.getElementById('documentObservations').value;
        
        if (!fileInput.files || fileInput.files.length === 0) {
            showAlert('Debes seleccionar un archivo', 'warning');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'uploadDocument');
        formData.append('solicitud_id', currentSolicitudId);
        formData.append('tipo_documento_id', tipoDocumentoId);
        formData.append('documento', fileInput.files[0]);
        formData.append('observaciones', observaciones);
        
        const btnUpload = document.getElementById('btnUploadDocument');
        btnUpload.disabled = true;
        btnUpload.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subiendo...';
        
        fetch('../php/estudiantephp/documentos.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('Documento subido correctamente', 'success');
                uploadModal.hide();
                loadStudentDocuments();
            } else {
                showAlert(data.message || 'Error al subir el documento', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al conectar con el servidor', 'danger');
        })
        .finally(() => {
            btnUpload.disabled = false;
            btnUpload.textContent = 'Subir Documento';
        });
    }
    
    function viewDocument(documentoId) {
        const documento = documentosSubidos.find(d => d.documento_id == documentoId);
        if (!documento) return;
        
        document.getElementById('viewDocumentModalLabel').textContent = documento.nombre_documento;
        document.getElementById('documentStatusBadge').innerHTML = getStatusBadge(documento.estado);
        
        document.getElementById('documentObservationsText').textContent = 
            documento.observaciones || 'Sin observaciones';
        
        document.getElementById('documentUploadDate').textContent = 
            new Date(documento.fecha_subida).toLocaleString();
        
        document.getElementById('documentValidationDate').textContent = 
            documento.fecha_validacion ? new Date(documento.fecha_validacion).toLocaleString() : '-';
        
        document.getElementById('btnDownloadDocument').href = 
            `../php/estudiantephp/documentos.php?action=downloadDocument&documento_id=${documentoId}`;
        
        const viewer = document.getElementById('documentViewer');
        viewer.innerHTML = '';
        
        const fileUrl = `../php/estudiantephp/documentos.php?action=viewDocument&documento_id=${documentoId}`;
        
        if (documento.tipo_archivo.includes('pdf')) {
            viewer.innerHTML = `<embed src="${fileUrl}" type="application/pdf" width="100%" height="500px">`;
        } else if (documento.tipo_archivo.includes('image')) {
            viewer.innerHTML = `<img src="${fileUrl}" class="img-fluid" alt="Documento">`;
        } else {
            viewer.innerHTML = `<div class="alert alert-info"><i class="bi bi-info-circle-fill me-2"></i>Vista previa no disponible. Descarga el documento para verlo.</div>`;
        }
        
        viewDocumentModal.show();
    }
    
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
});
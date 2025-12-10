export async function generatePrintHTML(currentUser, photoPreview, language = 'fr') {
    // Fonctions utilitaires
    function formatDate(date) {
        if (!date) return language === 'fr' ? 'Non défini' : 'غير محدد';
        return new Date(date).toLocaleDateString(
            language === 'fr' ? 'fr-FR' : 'ar-TN',
            {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            }
        );
    }
    async function loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = src;
            img.onload = () => resolve(src);
            img.onerror = () => {
                console.warn(`Failed to load image: ${src}`);
                resolve('/default-avatar.png');
            };
        });
    }
    function isValidPhoto(photo) {
        if (!photo) return false;
        if (photo.startsWith('data:image/') || photo.startsWith('blob:')) return true;
        const validFormats = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'];
        return validFormats.some((format) => photo.toLowerCase().endsWith(format));
    }
    function getPhotoUrl(photo) {
        if (!photo) return '/default-avatar.png';
        if (photo.startsWith('data:image/') || photo.startsWith('blob:')) {
            try {
                if (photo.startsWith('data:image/')) {
                    const base64Part = photo.split(',')[1];
                    atob(base64Part);
                }
                return photo;
            } catch (e) {
                console.error('Invalid base64 image:', e);
                return '/default-avatar.png';
            }
        }
        if (photo.startsWith('/storage/')) {
            return `${window.location.origin}${photo}`;
        }
        return `${window.location.origin}/storage/photos/${photo}`;
    }
    const photoSrc = await loadImage(
        photoPreview || (currentUser.photo && isValidPhoto(currentUser.photo) ? getPhotoUrl(currentUser.photo) : '/default-avatar.png')
    );
    // Déterminer la direction du texte en fonction de la langue
    const dir = language === 'ar' ? 'rtl' : 'ltr';
    // Contenu conditionnel selon la langue
    const personalInfoTitle = language === 'fr' ? 'Informations Personnelles' : 'المعلومات الشخصية';
    const contactTitle = language === 'fr' ? 'Contact' : 'الاتصال';
    const statusTitle = language === 'fr' ? 'Statut Professionnel' : 'الحالة المهنية';
    const observationsTitle = language === 'fr' ? 'Observations' : 'ملاحظات';
    const printedDateLabel = language === 'fr' ? 'Imprimé le :' : 'تاريخ الطباعة:';
    const profileTitle = language === 'fr' ? 'Profil Utilisateur ATFP' : 'جذاذة مستخدم (الإدارة المركزية)';
    const matriculeLabel = language === 'fr' ? 'Matricule:' : 'المعرف الوحيد:';
    const rolesLabel = language === 'fr' ? 'Rôles:' : 'الخطة:';
    const statusLabel = language === 'fr' ? 'Statut:' : 'الحالة:';
    const noRole = language === 'fr' ? 'Aucun rôle' : 'لا توجد خطة';
    const activeStatus = language === 'fr' ? 'Actif' : 'نشط';
    const inactiveStatus = language === 'fr' ? 'Inactif' : 'غير نشط';
    // Fonction pour formater les rôles selon la langue
    function formatRoleName(role) {
        return language === 'ar' ? (role.name_ar || role.name || 'N/A') : (role.name || 'N/A');
    }

    // Champs communs - MODIFIÉ: Les rôles et statut sont maintenant dans une section séparée à droite
    const commonFields = `
        <div class="user-info-header">
            <img src="${photoSrc}" alt="${language === 'fr' ? 'Photo de ' + (currentUser.nom_fr || 'Utilisateur') : 'صورة ' + (currentUser.nom_ar || 'المستخدم')}" />
            <div class="user-details">
                <h2>${language === 'fr'
        ? (currentUser.nom_fr || 'Non défini') + ' ' + (currentUser.prenom_fr || '')
        : (currentUser.prenom_ar || 'غير محدد') + ' ' + (currentUser.nom_ar || '')}</h2>
                <div class="matricule">${matriculeLabel} ${currentUser.matricule || (language === 'fr' ? 'Non défini' : 'غير محدد')}</div>
            </div>
            <div class="right-section">
                <div class="user-roles">
                    ${(currentUser.roles || []).map((role) => `<span class="role-tag">${formatRoleName(role)}</span>`).join('') || `<span class="role-tag">${noRole}</span>`}
                </div>
                <div class="status ${currentUser.statut === 'Actif' ? 'active' : 'inactive'}">
                    ${currentUser.statut === 'Actif' ? activeStatus : inactiveStatus}
                </div>
            </div>
        </div>
    `;
    // Champs spécifiques à la langue
    const personalInfoFields = language === 'fr' ? `
        <div class="info-card full-width">
            <div class="info-card-title">Identité Française</div>
            <div class="info-item">
                <label>Prénom:</label>
                <span>${currentUser.prenom_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Nom:</label>
                <span>${currentUser.nom_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Genre:</label>
                <span>${currentUser.genre_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Etat Civil:</label>
                <span>${currentUser.etat_civil_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Date de Naissance:</label>
                <span>${formatDate(currentUser.date_naissance)}</span>
            </div>
            <div class="info-item">
                <label>Lieu de Naissance:</label>
                <span>${currentUser.lieu_naissance_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Nationalité:</label>
                <span>${currentUser.nationalite_fr || 'Non défini'}</span>
            </div>
        </div>
        <div class="info-card full-width">
            <div class="info-card-title">Carte d'Identité</div>
            <div class="info-item">
                <label>Numéro CIN:</label>
                <span>${currentUser.cin || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Date Délivrance CIN:</label>
                <span>${formatDate(currentUser.date_cin)}</span>
            </div>
            <div class="info-item">
                <label>Lieu Délivrance CIN:</label>
                <span>${currentUser.lieu_cin_fr || 'Non défini'}</span>
            </div>
        </div>
    ` : `
        <div class="info-card full-width">
            <div class="info-card-title">الهوية العربية</div>
            <div class="info-item">
                <label>الاسم:</label>
                <span>${currentUser.prenom_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>اللقب:</label>
                <span>${currentUser.nom_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>الجنس:</label>
                <span>${currentUser.genre_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>الحالة المدنية:</label>
                <span>${currentUser.etat_civil_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>تاريخ الولادة:</label>
                <span>${formatDate(currentUser.date_naissance)}</span>
            </div>
            <div class="info-item">
                <label>مكان الولادة:</label>
                <span>${currentUser.lieu_naissance_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>الجنسية:</label>
                <span>${currentUser.nationalite_ar || 'تونسية'}</span>
            </div>
            <div class="info-item">
                <label>رقم ب ت و:</label>
                <span>${currentUser.cin || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>تاريخ إصدار ب ت و:</label>
                <span>${formatDate(currentUser.date_cin)}</span>
            </div>
            <div class="info-item">
                <label>مكان إصدار ب ت و:</label>
                <span>${currentUser.lieu_cin_ar || 'غير محدد'}</span>
            </div>
        </div>
    `;
    const contactFields = language === 'fr' ? `
        <div class="info-card full-width">
            <div class="info-card-title">Coordonnées & Adresse</div>
            <div class="info-item">
                <label>Email:</label>
                <span>${currentUser.email || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Téléphone 1:</label>
                <span>${currentUser.telephone_1 || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Téléphone 2:</label>
                <span>${currentUser.telephone_2 || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Adresse:</label>
                <span>${currentUser.adresse_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Gouvernorat:</label>
                <span>${currentUser.gouvernorat_fr || 'Non défini'}</span>
            </div>
            <div class="info-item">
                <label>Délégation:</label>
                <span>${currentUser.delegation_fr || 'Non défini'}</span>
            </div>
        </div>
    ` : `
        <div class="info-card full-width">
            <div class="info-card-title">العنوان</div>
            <div class="info-item">
                <label>البريد الإلكتروني:</label>
                <span>${currentUser.email || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>الهاتف 1:</label>
                <span>${currentUser.telephone_1 || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>الهاتف 2:</label>
                <span>${currentUser.telephone_2 || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>العنوان:</label>
                <span>${currentUser.adresse_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>الولاية:</label>
                <span>${currentUser.gouvernorat_ar || 'غير محدد'}</span>
            </div>
            <div class="info-item">
                <label>المعتمدية:</label>
                <span>${currentUser.delegation_ar || 'غير محدد'}</span>
            </div>
        </div>
    `;
    const statusFields = language === 'fr' ? `
        <div class="info-card">
            <div class="info-card-title">Statut</div>
            <div class="info-item">
                <label>Statut:</label>
                <span>${currentUser.statut || 'Inactif'}</span>
            </div>
            <div class="info-item">
                <label>Date recrutement:</label>
                <span>${formatDate(currentUser.date_recrutement)}</span>
            </div>
            <div class="info-item">
                <label>Date fin service:</label>
                <span>${formatDate(currentUser.date_fin_service)}</span>
            </div>
        </div>
        <div class="info-card">
            <div class="info-card-title">Inactivité</div>
            <div class="info-item">
                <label>Cause:</label>
                <span>${currentUser.cause_inactivite_fr || 'Non défini'}</span>
            </div>
        </div>
    ` : `
        <div class="info-card">
            <div class="info-card-title">الحالة</div>
            <div class="info-item">
                <label>الحالة:</label>
                <span>${currentUser.statut === 'Actif' ? 'نشط' : 'غير نشط'}</span>
            </div>
            <div class="info-item">
                <label>تاريخ التوظيف:</label>
                <span>${formatDate(currentUser.date_recrutement)}</span>
            </div>
            <div class="info-item">
                <label>تاريخ نهاية الخدمة:</label>
                <span>${formatDate(currentUser.date_fin_service)}</span>
            </div>
        </div>
        <div class="info-card">
            <div class="info-card-title">سبب عدم النشاط</div>
            <div class="info-item">
                <label>السبب:</label>
                <span>${currentUser.cause_inactivite_ar || 'غير محدد'}</span>
            </div>
        </div>
    `;
    const observationsFields = language === 'fr' ? `
        <div class="observation-card">
            <div class="info-item">
                <label>Observation:</label>
                <span>${currentUser.observation_fr || 'Aucune observation'}</span>
            </div>
        </div>
    ` : `
        <div class="observation-card">
            <div class="info-item">
                <label>ملاحظات:</label>
                <span>${currentUser.observation_ar || 'لا توجد ملاحظات'}</span>
            </div>
        </div>
    `;
    return `
<!DOCTYPE html>
<html lang="${language}" dir="${dir}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${profileTitle} - ${language === 'fr'
        ? (currentUser.nom_fr || 'Utilisateur')
        : (currentUser.prenom_ar || 'المستخدم')}</title>
  <style>
    @font-face {
      font-family: 'Montserrat-Arabic-Regular';
      src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
      font-weight: normal;
      font-style: normal;
      font-display: swap;
    }
    :root {
      --primary-color: #4361ee;
      --primary-light: #e6f0ff;
      --secondary-color: #3f37c9;
      --light-gray: #f8f9fa;
      --medium-gray: #e9ecef;
      --dark-gray: #212529;
      --border-color: #ced4da;
      --success-color: #4cc9f0;
      --danger-color: #f72585;
      --warning-color: #f8961e;
      --info-color: #4895ef;
      --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      --border-radius: 8px;
    }
    body {
      font-family: 'Segoe UI', 'Arial', sans-serif;
      margin: 0;
      padding: 5mm;
      color: var(--dark-gray);
      line-height: 1.4;
      background-color: #fff;
      direction: ${dir};
      text-align: left;
    }
    .print-container {
      max-width: 210mm;
      margin: 0 auto;
      background: white;
      padding: 0;
    }
    .print-header {
      text-align: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--primary-color);
    }
    .print-header h1 {
      margin: 0;
      font-size: 1.8rem;
      color: var(--primary-color);
      font-weight: 600;
    }
    .print-header .date {
      font-size: 0.9rem;
      color: #6c757d;
      margin: 5px 0;
    }
    .print-section {
      margin-bottom: 15px;
      page-break-inside: avoid;
    }
    .section-title {
      font-size: 1.3rem;
      color: var(--secondary-color);
      margin-bottom: 10px;
      padding-bottom: 5px;
      border-bottom: 2px solid var(--primary-light);
      text-align: ${dir === 'rtl' ? 'right' : 'left'};
      font-weight: 500;
    }
    .user-info-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      padding: 15px;
      background-color: white;
      border-radius: var(--border-radius);
      border: 1px solid var(--border-color);
      box-shadow: var(--box-shadow);
      direction: ${dir};
    }
    .user-info-header img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid var(--primary-color);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      order: 1;
    }
    .user-info-header .user-details {
      flex: 1;
      order: 2;
      margin: 0 20px;
    }
    .user-info-header .user-details h2 {
      margin: 0 0 5px 0;
      font-size: 1.5rem;
      color: var(--dark-gray);
      font-weight: 600;
      text-align: ${dir === 'rtl' ? 'right' : 'left'};
    }
    .user-info-header .user-details .matricule {
      font-size: 0.9rem;
      color: #6c757d;
      text-align: ${dir === 'rtl' ? 'right' : 'left'};
    }
    .user-info-header .right-section {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 10px;
      order: 3;
      min-width: 200px;
    }
    .user-info-header .right-section .user-roles {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      justify-content: ${dir === 'rtl' ? 'flex-start' : 'flex-end'};
    }
    .user-info-header .right-section .role-tag {
      background-color: var(--primary-light);
      color: var(--primary-color);
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 500;
      border: 1px solid var(--primary-color);
    }
    .user-info-header .right-section .status {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      color: #fff;
      text-align: center;
    }
    .user-info-header .right-section .status.active {
      background-color: var(--success-color);
    }
    .user-info-header .right-section .status.inactive {
      background-color: var(--danger-color);
    }
    /* Ajustements pour la version arabe */
    html[dir="rtl"] .user-info-header .right-section {
      align-items: flex-start;
    }
    html[dir="rtl"] .user-info-header .right-section .user-roles {
      justify-content: flex-start;
    }
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 12px;
      margin-bottom: 15px;
    }
    .info-card {
      padding: 12px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: white;
      box-shadow: var(--box-shadow);
    }
    .info-card.full-width {
      grid-column: 1 / -1;
    }
    .info-card-title {
      font-size: 1rem;
      color: var(--primary-color);
      margin-bottom: 8px;
      padding-bottom: 5px;
      border-bottom: 1px dashed var(--border-color);
      font-weight: 500;
      text-align: ${dir === 'rtl' ? 'right' : 'left'};
    }
    .info-item {
      display: flex;
      flex-direction: row;
      align-items: baseline;
      padding: 4px 0;
      gap: 5px;
    }
    .info-item label {
      font-weight: 500;
      color: var(--dark-gray);
      font-size: 0.85rem;
      min-width: 100px;
      text-align: ${dir === 'rtl' ? 'right' : 'left'};
    }
    .info-item span {
      color: #495057;
      font-size: 0.85rem;
      word-break: break-word;
      flex: 1;
      text-align: ${dir === 'rtl' ? 'right' : 'left'};
    }
    .roles-section {
      padding: 12px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: white;
      box-shadow: var(--box-shadow);
    }
    .roles-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: flex-start;
    }
    .role-tag {
      background-color: var(--primary-light);
      color: var(--primary-color);
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      border: 1px solid var(--border-color);
    }
    .observation-card {
      padding: 12px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: white;
      box-shadow: var(--box-shadow);
      margin-bottom: 15px;
    }
    /* Styles spécifiques pour l'arabe */
    html[dir="rtl"] body {
      font-family: 'Montserrat-Arabic-Regular', sans-serif;
    }
    @media print {
      body {
        padding: 0;
      }
      .print-container {
        box-shadow: none;
      }
      .print-header {
        border-bottom: 3px solid var(--primary-color);
      }
      .user-info-header img {
        border: 3px solid var(--primary-color);
      }
      @page {
        margin: 10mm;
        size: A4;
      }
      .print-section {
        break-inside: avoid;
      }
    }
  </style>
</head>
<body>
  <div class="print-container">
    <div class="print-header">
      <h1>${profileTitle}</h1>
      <div class="date">${printedDateLabel} ${new Date().toLocaleDateString(
        language === 'fr' ? 'fr-FR' : 'ar-TN',
        {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    )}</div>
    </div>
    ${commonFields}
    <div class="print-section">
      <div class="section-title">${personalInfoTitle}</div>
      <div class="info-grid">
        ${personalInfoFields}
      </div>
    </div>
    <div class="print-section">
      <div class="section-title">${contactTitle}</div>
      <div class="info-grid">
        ${contactFields}
      </div>
    </div>
    <div class="print-section">
      <div class="section-title">${statusTitle}</div>
      <div class="info-grid">
        ${statusFields}
      </div>
    </div>
    <div class="print-section">
      <div class="section-title">${observationsTitle}</div>
      ${observationsFields}
    </div>
  </div>
</body>
</html>
`;

}

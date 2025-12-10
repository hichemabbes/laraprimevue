<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ListesSeeder extends Seeder
{
    public function run()
    {
        // Désactiver temporairement les contraintes de clé étrangère
        Schema::disableForeignKeyConstraints();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider la table si elle contient des données
        DB::table('listes')->truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Schema::enableForeignKeyConstraints();

        // Liste des données à insérer
        $listes = [

            [
                'nom_fr' => 'Gouvernorats',
                'nom_ar' => 'الولايات',
                'options' => [
                    ['nom_fr' => 'Ariana', 'nom_ar' => 'أريانة', 'valeur' => 'ariana'],
                    ['nom_fr' => 'Béja', 'nom_ar' => 'باجة', 'valeur' => 'beja'],
                    ['nom_fr' => 'Ben Arous', 'nom_ar' => 'بن عروس', 'valeur' => 'ben_arous'],
                    ['nom_fr' => 'Bizerte', 'nom_ar' => 'بنزرت', 'valeur' => 'bizerte'],
                    ['nom_fr' => 'Gabès', 'nom_ar' => 'قابس', 'valeur' => 'gabes'],
                    ['nom_fr' => 'Gafsa', 'nom_ar' => 'قفصة', 'valeur' => 'gafsa'],
                    ['nom_fr' => 'Jendouba', 'nom_ar' => 'جندوبة', 'valeur' => 'jendouba'],
                    ['nom_fr' => 'Kairouan', 'nom_ar' => 'القيروان', 'valeur' => 'kairouan'],
                    ['nom_fr' => 'Kasserine', 'nom_ar' => 'القصرين', 'valeur' => 'kasserine'],
                    ['nom_fr' => 'Kébili', 'nom_ar' => 'قبلي', 'valeur' => 'kebili'],
                    ['nom_fr' => 'La Manouba', 'nom_ar' => 'منوبة', 'valeur' => 'manouba'],
                    ['nom_fr' => 'Le Kef', 'nom_ar' => 'الكاف', 'valeur' => 'le_kef'],
                    ['nom_fr' => 'Mahdia', 'nom_ar' => 'المهدية', 'valeur' => 'mahdia'],
                    ['nom_fr' => 'Médenine', 'nom_ar' => 'مدنين', 'valeur' => 'medenine'],
                    ['nom_fr' => 'Monastir', 'nom_ar' => 'المنستير', 'valeur' => 'monastir'],
                    ['nom_fr' => 'Nabeul', 'nom_ar' => 'نابل', 'valeur' => 'nabeul'],
                    ['nom_fr' => 'Sfax', 'nom_ar' => 'صفاقس', 'valeur' => 'sfax'],
                    ['nom_fr' => 'Sidi Bouzid', 'nom_ar' => 'سيدي بوزيد', 'valeur' => 'sidi_bouzid'],
                    ['nom_fr' => 'Siliana', 'nom_ar' => 'سليانة', 'valeur' => 'siliana'],
                    ['nom_fr' => 'Sousse', 'nom_ar' => 'سوسة', 'valeur' => 'sousse'],
                    ['nom_fr' => 'Tataouine', 'nom_ar' => 'تطاوين', 'valeur' => 'tataouine'],
                    ['nom_fr' => 'Tozeur', 'nom_ar' => 'توزر', 'valeur' => 'tozeur'],
                    ['nom_fr' => 'Tunis', 'nom_ar' => 'تونس', 'valeur' => 'tunis'],
                    ['nom_fr' => 'Zaghouan', 'nom_ar' => 'زغوان', 'valeur' => 'zaghouan'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 2,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Vacances et JF ',
                'nom_ar' => 'العطل و الاعياد',
                'options' => [
                    ['nom_fr' => 'Vacance de mi-trimestre (1er trimestre)', 'nom_ar' => 'عطلة نصف الثلاثي الأول', 'valeur' => 'mi_trimestre_1'],
                    ['nom_fr' => 'Vacance d\'hiver et Nouvel An', 'nom_ar' => 'عطلة الشتاء ورأس السنة', 'valeur' => 'hiver_nouvel_an'],
                    ['nom_fr' => 'Vacance de deuxième mi-trimestre', 'nom_ar' => 'عطلة نصف الثلاثي الثاني', 'valeur' => 'mi_trimestre_2'],
                    ['nom_fr' => 'Vacance de mi-trimestre (2e trimestre)', 'nom_ar' => 'عطلة نصف الثلاثي الثاني', 'valeur' => 'mi_trimestre_2_bis'],
                    ['nom_fr' => 'Vacance de printemps', 'nom_ar' => 'عطلة الربيع', 'valeur' => 'printemps'],
                    ['nom_fr' => 'Aïd al-Fitr', 'nom_ar' => 'عيد الفطر', 'valeur' => 'aid_al_fitr'],
                    ['nom_fr' => 'Aïd al-Adha', 'nom_ar' => 'عيد الأضحى', 'valeur' => 'aid_al_adha'],
                    ['nom_fr' => 'Vacance de fin de l\'année', 'nom_ar' => 'عطلة نهاية السنة الدراسية', 'valeur' => 'fin_annee'],
                    ['nom_fr' => 'Fête de la Révolution', 'nom_ar' => 'عيد الثورة', 'valeur' => 'revolution'],
                    ['nom_fr' => 'Fête de l\'Indépendance', 'nom_ar' => 'عيد الاستقلال', 'valeur' => 'independance'],
                    ['nom_fr' => 'Journée des Martyrs', 'nom_ar' => 'عيد الشهداء', 'valeur' => 'martyrs'],
                    ['nom_fr' => 'Fête du Travail', 'nom_ar' => 'عيد الشغل', 'valeur' => 'travail'],
                    ['nom_fr' => 'Fête de la République', 'nom_ar' => 'عيد الجمهورية', 'valeur' => 'republique'],
                    ['nom_fr' => 'Fête de la Femme', 'nom_ar' => 'عيد المرأة', 'valeur' => 'femme'],
                    ['nom_fr' => 'Fête de l\'Évacuation', 'nom_ar' => 'عيد الجلاء', 'valeur' => 'evacuation'],
                    ['nom_fr' => 'Nouvel An de l\'Hégire (Ras El Am El Hijri)', 'nom_ar' => 'رأس السنة الهجرية', 'valeur' => 'hegire'],
                    ['nom_fr' => 'Mouled (Naissance du Prophète)', 'nom_ar' => 'المولد النبوي الشريف', 'valeur' => 'mouled'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 3,
                'statut' => 'Actif',
                'description' => null,
            ],


            [
                'nom_fr' => 'Diplomes',
                'nom_ar' => 'الشهائد',
                'options' => [
                    ['nom_fr' => 'Certificat d\'Aptitude Professionnelle', 'nom_ar' => 'شهادة الكفاءة المهنية', 'valeur' => 'cap'],
                    ['nom_fr' => 'Brevet de Technicien Professionnel', 'nom_ar' => 'مؤهل تقني مهني', 'valeur' => 'btp'],
                    ['nom_fr' => 'Brevet de Technicien Supérieur', 'nom_ar' => 'مؤهل تقني سامي', 'valeur' => 'bts'],
                    ['nom_fr' => 'Certificat de Compétence', 'nom_ar' => 'شهادة مهارة', 'valeur' => 'cc'],
                    ['nom_fr' => 'Certificat de Fin d\'Apprentissage', 'nom_ar' => 'شهادة إنهاء التدريب', 'valeur' => 'cfa'],
                    ['nom_fr' => 'Certificat de Formation Professionnelle', 'nom_ar' => 'شهادة تكوين مهني', 'valeur' => 'cfp'],
                    ['nom_fr' => 'Formation Technique Professionnelle', 'nom_ar' => 'تكوين تقني مهني', 'valeur' => 'ftp'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 6,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Types Documents Spécialités',
                'nom_ar' => 'عنوان المرجع',
                'options' => [
                    ['nom_fr' => 'Programme d\'étude', 'nom_ar' => 'برنامج التكوين', 'valeur' => 'programme_etude'],
                    ['nom_fr' => 'Dossier d\'homologation', 'nom_ar' => 'ملف التنظير', 'valeur' => 'dossier_homologation'],
                    ['nom_fr' => 'Guide pédagogique', 'nom_ar' => 'الدليل البيداغوجي', 'valeur' => 'guide_pedagogique'],
                    ['nom_fr' => 'Guide d\'évaluation', 'nom_ar' => 'دليل التقييم', 'valeur' => 'guide_evaluation'],
                    ['nom_fr' => 'Guide d\'organisation', 'nom_ar' => 'دليل التنظيم', 'valeur' => 'guide_organisation'],
                    ['nom_fr' => 'Guide matériels', 'nom_ar' => 'دليل التجهيزات', 'valeur' => 'guide_materiels'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 7,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Types Documents Modules',
                'nom_ar' => 'أنواع وثائق الوحدات',
                'options' => [
                    ['nom_fr' => 'Support de cours', 'nom_ar' => 'دعــم الدرس', 'valeur' => 'support_cours'],
                    ['nom_fr' => 'Exercices', 'nom_ar' => 'تمارين تطبيقية', 'valeur' => 'exercices'],
                    ['nom_fr' => 'Corrigés', 'nom_ar' => 'الإصلاحات', 'valeur' => 'corriges'],
                    ['nom_fr' => 'TP / Travaux pratiques', 'nom_ar' => 'أعمال تطبيقية', 'valeur' => 'travaux_pratiques'],
                    ['nom_fr' => 'Évaluation continue', 'nom_ar' => 'تقييم مستمر', 'valeur' => 'evaluation_continue'],
                    ['nom_fr' => 'Épreuve de synthèse', 'nom_ar' => 'اختبار تركيبي', 'valeur' => 'epreuve_synthese'],
                    ['nom_fr' => 'Grille d\'évaluation', 'nom_ar' => 'شبكة التقييم', 'valeur' => 'grille_evaluation'],
                    ['nom_fr' => 'Fiche pédagogique', 'nom_ar' => 'الورقة البيداغوجية', 'valeur' => 'fiche_pedagogique'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 8,
                'statut' => 'Actif',
                'description' => null,
            ],

            [
                'nom_fr' => 'Niveaux Etudes Personnels',
                'nom_ar' => 'مستوى تعليم الموظف',
                'options' => [
                    ['nom_fr' => 'Niveau de la première étape de l\'enseignement secondaire', 'nom_ar' => 'مستوى المرحلة الأولى من التعليم الثّانوي', 'valeur' => 'secondaire_1'],
                    ['nom_fr' => 'Certificat de compétence professionnelle', 'nom_ar' => 'مؤهل التعليم الثّانوي المهني (شهادة الكفاءة المهنيّة)', 'valeur' => 'ccp'],
                    ['nom_fr' => 'Niveau de la deuxième étape de l\'enseignement secondaire', 'nom_ar' => 'مستوى المرحلة الثّانيّة من التعليم الثّانوي', 'valeur' => 'secondaire_2'],
                    ['nom_fr' => 'Baccalauréat', 'nom_ar' => 'شهادة الباكالوريا', 'valeur' => 'bac'],
                    ['nom_fr' => 'Certificat de technicien', 'nom_ar' => 'شهادة التقني', 'valeur' => 'technicien'],
                    ['nom_fr' => 'Niveau de la deuxième année de la première étape de l\'enseignement supérieur', 'nom_ar' => 'مستوى السنة الثّانية من المرحلة الأولى من التعليم العالي', 'valeur' => 'superieur_1_annee_2'],
                    ['nom_fr' => 'Diplôme de la première étape de l\'enseignement supérieur', 'nom_ar' => 'شهادة المرحلة الأولى من التعليم العالي', 'valeur' => 'superieur_1'],
                    ['nom_fr' => 'Ingénieur assistant', 'nom_ar' => 'مهندس مساعد', 'valeur' => 'ingenieur_assistant'],
                    ['nom_fr' => 'Niveau de la deuxième année de la deuxième étape de l\'enseignement supérieur', 'nom_ar' => 'مستوى السنة الثّانيّـة من المرحلة الـثّانـيّـة للتعليم العالي', 'valeur' => 'superieur_2_annee_2'],
                    ['nom_fr' => 'Maîtrise', 'nom_ar' => 'الأستاذيّة', 'valeur' => 'maitrise'],
                    ['nom_fr' => 'Ingénieur technicien', 'nom_ar' => 'مهندس تقني', 'valeur' => 'ingenieur_technicien'],
                    ['nom_fr' => 'Ingénieur principal', 'nom_ar' => 'مهندس أول', 'valeur' => 'ingenieur_principal'],
                    ['nom_fr' => 'Troisième cycle de l\'enseignement supérieur (ou Diplôme d\'Études Approfondies)', 'nom_ar' => 'حلـقة ثالثة من التّـعـليم العالي (أو شهادة الدّراسات المعمّقة)', 'valeur' => 'superieur_3'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 8,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Types Contrats PersonnelsDirectionCentrale',
                'nom_ar' => 'أنواع عقود الموظفين',
                'options' => [
                    ['nom_fr' => 'Contrat à Durée Indéterminée (CDI)', 'nom_ar' => 'عقد لمدة غير محدودة', 'valeur' => 'cdi'],
                    ['nom_fr' => 'Contrat à Durée Déterminée (CDD)', 'nom_ar' => 'عقد لمدة محدودة', 'valeur' => 'cdd'],
                    ['nom_fr' => 'Contrat de Vacation', 'nom_ar' => 'عقد عمل مؤقت', 'valeur' => 'vacation'],
                    ['nom_fr' => 'Contrat d\'Intérim', 'nom_ar' => 'عقد نيابة', 'valeur' => 'interim'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 9,
                'statut' => 'Actif',
                'description' => null,
            ],

            [
                'nom_fr' => 'Formules de formation',
                'nom_ar' => 'صيغ التكوين',
                'options' => [
                    ['nom_fr' => 'Formations en centre', 'nom_ar' => 'تكوين بالمركز', 'valeur' => 'centre'],
                    ['nom_fr' => 'Formations en alternance', 'nom_ar' => 'تكوين بالتناوب', 'valeur' => 'alternance'],
                    ['nom_fr' => 'Formations en alternance à distance', 'nom_ar' => 'تكوين بالتناوب عن بعد', 'valeur' => 'alternance_distance'],
                    ['nom_fr' => 'Formations à distance', 'nom_ar' => 'تكوين عن بعد', 'valeur' => 'distance'],
                    ['nom_fr' => 'Formations courtes', 'nom_ar' => 'تكوين قصير المدى', 'valeur' => 'courtes'],
                    ['nom_fr' => 'Formations POE', 'nom_ar' => 'تكوين POE', 'valeur' => 'poe'],
                    ['nom_fr' => 'Validation des Acquis de l’Expérience (VAE)', 'nom_ar' => 'إثبات مكتسبات الخبرة (VAE)', 'valeur' => 'vae'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 13,
                'statut' => 'Actif',
                'description' => null,
            ],




            [
                'nom_fr' => 'Modes Formations',
                'nom_ar' => 'أنماط التكوين',
                'options' => [
                    ['nom_fr' => 'Résidentiel', 'nom_ar' => 'بالمركز', 'valeur' => 'residentiel'],
                    ['nom_fr' => 'Alternance', 'nom_ar' => 'بالتداول', 'valeur' => 'alternance'],
                    ['nom_fr' => 'Apprentissage (F16)', 'nom_ar' => 'بالتداول (F16)', 'valeur' => 'apprentissage_f16'],
                    ['nom_fr' => 'Apprentissage (F8)', 'nom_ar' => 'بالتداول (F8)', 'valeur' => 'apprentissage_f8'],
                    ['nom_fr' => 'Apprentissage (F4)', 'nom_ar' => 'بالتداول (F4)', 'valeur' => 'apprentissage_f4'],
                    ['nom_fr' => 'Apprentissage (F0)', 'nom_ar' => 'بالتداول (F0)', 'valeur' => 'apprentissage_f0'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 11,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Types Documents Administratifs PersonnelsDirectionCentrale',
                'nom_ar' => 'أنواع الوثائق الإدارية',
                'options' => [
                    ['nom_fr' => 'Copie CIN', 'nom_ar' => 'نسخة بطاقة التعريف', 'valeur' => 'cin'],
                    ['nom_fr' => 'CV', 'nom_ar' => 'السيرة الذاتية', 'valeur' => 'cv'],
                    ['nom_fr' => 'Copie diplôme', 'nom_ar' => 'نسخة الشهادة', 'valeur' => 'diplome'],
                    ['nom_fr' => 'Attestation de travail', 'nom_ar' => 'شهادة عمل', 'valeur' => 'attestation_travail'],
                    ['nom_fr' => 'Fiche de paie', 'nom_ar' => 'إيصال الأجر', 'valeur' => 'fiche_paie'],
                    ['nom_fr' => 'Contrat de travail', 'nom_ar' => 'عقد العمل', 'valeur' => 'contrat_travail'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 12,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Types Documents Périodiques PersonnelsDirectionCentrale',
                'nom_ar' => 'أنواع الوثائق الدورية',
                'options' => [
                    ['nom_fr' => 'Certificat médical', 'nom_ar' => 'شهادة طبية', 'valeur' => 'certificat_medical'],
                    ['nom_fr' => 'Attestation d\'assurance', 'nom_ar' => 'شهادة التأمين', 'valeur' => 'attestation_assurance'],
                    ['nom_fr' => 'Déclaration fiscale', 'nom_ar' => 'تصريح ضريبي', 'valeur' => 'declaration_fiscale'],
                    ['nom_fr' => 'Évaluation annuelle', 'nom_ar' => 'تقييم سنوي', 'valeur' => 'evaluation_annuelle'],
                    ['nom_fr' => 'Attestation de formation', 'nom_ar' => 'شهادة تكوين', 'valeur' => 'attestation_formation'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 13,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Niveaux Scolaire StagiairesCentres',
                'nom_ar' => 'المستوى الدراسي للمتربصين',
                'options' => [
                    ['nom_fr' => 'Première année primaire', 'nom_ar' => 'الأولى إبتدائي', 'valeur' => 'primaire_1'],
                    ['nom_fr' => 'Deuxième année primaire', 'nom_ar' => 'الثانية إبتدائي', 'valeur' => 'primaire_2'],
                    ['nom_fr' => 'Troisième année primaire', 'nom_ar' => 'الثالثة إبتدائي', 'valeur' => 'primaire_3'],
                    ['nom_fr' => 'Quatrième année primaire', 'nom_ar' => 'الرابعة إبتدائي', 'valeur' => 'primaire_4'],
                    ['nom_fr' => 'Cinquième année primaire', 'nom_ar' => 'الخامسة إبتدائي', 'valeur' => 'primaire_5'],
                    ['nom_fr' => 'Sixième année primaire', 'nom_ar' => 'السادسة إبتدائي', 'valeur' => 'primaire_6'],
                    ['nom_fr' => 'Septième année de base (normal)', 'nom_ar' => 'السابعة أساسي عادي', 'valeur' => 'base_7_normal'],
                    ['nom_fr' => 'Huitième année de base (normal)', 'nom_ar' => 'الثامنة أساسي عادي', 'valeur' => 'base_8_normal'],
                    ['nom_fr' => 'Neuvième année de base (normal)', 'nom_ar' => 'التاسعة أساسي عادي', 'valeur' => 'base_9_normal'],
                    ['nom_fr' => 'Septième année de base (technique)', 'nom_ar' => 'السابعة أساسي تقني', 'valeur' => 'base_7_technique'],
                    ['nom_fr' => 'Huitième année de base (technique)', 'nom_ar' => 'الثامنة أساسي تقني', 'valeur' => 'base_8_technique'],
                    ['nom_fr' => 'Neuvième année de base (technique)', 'nom_ar' => 'التاسعة أساسي تقني', 'valeur' => 'base_9_technique'],
                    ['nom_fr' => 'Première année secondaire', 'nom_ar' => 'الأولى ثانوي', 'valeur' => 'secondaire_1'],
                    ['nom_fr' => 'Deuxième année secondaire', 'nom_ar' => 'الثانية ثانوي', 'valeur' => 'secondaire_2'],
                    ['nom_fr' => 'Troisième année secondaire', 'nom_ar' => 'الثالثة ثانوي', 'valeur' => 'secondaire_3'],
                    ['nom_fr' => 'Quatrième année secondaire', 'nom_ar' => 'الرابعة ثانوي', 'valeur' => 'secondaire_4'],
                    ['nom_fr' => 'Baccalauréat', 'nom_ar' => 'باكالوريا', 'valeur' => 'bac'],
                    ['nom_fr' => 'Première année universitaire', 'nom_ar' => 'الأولى جامعي', 'valeur' => 'universitaire_1'],
                    ['nom_fr' => 'Deuxième année universitaire', 'nom_ar' => 'الثانية جامعي', 'valeur' => 'universitaire_2'],
                    ['nom_fr' => 'Troisième année universitaire', 'nom_ar' => 'الثالثة جامعي', 'valeur' => 'universitaire_3'],
                    ['nom_fr' => 'Quatrième année universitaire', 'nom_ar' => 'الرابعة جامعي', 'valeur' => 'universitaire_4'],
                    ['nom_fr' => 'Licence', 'nom_ar' => 'الإجازة', 'valeur' => 'licence'],
                    ['nom_fr' => 'Master ou plus', 'nom_ar' => 'الماجستار فما فوق', 'valeur' => 'master_plus'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 14,
                'statut' => 'Actif',
                'description' => null,
            ],

            [
                'nom_fr' => 'Statuts Centres',
                'nom_ar' => 'وضعية المراكز',
                'options' => [
                    ['nom_fr' => 'Fonctionnel', 'nom_ar' => 'في الخدمة', 'valeur' => 'fonctionnel'],
                    ['nom_fr' => 'Non Fonctionnel', 'nom_ar' => 'خارج الخدمة', 'valeur' => 'non_fonctionnel'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 16,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Types Centres',
                'nom_ar' => 'أنواع المراكز',
                'options' => [
                    ['nom_fr' => 'CSF', 'nom_ar' => 'مركز قطاعي في الكوين', 'valeur' => 'csf'],
                    ['nom_fr' => 'CFAMT', 'nom_ar' => 'مركز تكوين في الصناعات التقليدية', 'valeur' => 'cfamt'],
                    ['nom_fr' => 'CFAP', 'nom_ar' => 'مركز تكوين و تدريب مهني', 'valeur' => 'cfap'],
                    ['nom_fr' => 'CFPTI', 'nom_ar' => 'مركز تكوين و النهوض بالعمل المستقل', 'valeur' => 'cfpti'],
                    ['nom_fr' => 'CJFR', 'nom_ar' => 'مركز الفتاة الريفية', 'valeur' => 'cjfr'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 17,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Classes Centres',
                'nom_ar' => 'أصناف المراكز',
                'options' => [
                    ['nom_fr' => 'Classe A', 'nom_ar' => 'الصنف أ', 'valeur' => 'classe_a'],
                    ['nom_fr' => 'Classe B', 'nom_ar' => 'الصنف ب', 'valeur' => 'classe_b'],
                    ['nom_fr' => 'Classe C', 'nom_ar' => 'الصنف ج', 'valeur' => 'classe_c'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 18,
                'statut' => 'Actif',
                'description' => null,
            ],
            [
                'nom_fr' => 'Etats Centres',
                'nom_ar' => 'حالة المركز',
                'options' => [
                    ['nom_fr' => 'Fonctionnel', 'nom_ar' => 'يعمل', 'valeur' => 'fonctionnel'],
                    ['nom_fr' => 'En arrêt temporaire', 'nom_ar' => 'في توقف مؤقت', 'valeur' => 'arret_temporaire'],
                    ['nom_fr' => 'Fermé', 'nom_ar' => 'مغلق', 'valeur' => 'ferme'],
                    ['nom_fr' => 'En projet', 'nom_ar' => 'في طور الإنجاز', 'valeur' => 'en_projet'],
                    ['nom_fr' => 'En rénovation', 'nom_ar' => 'في طور التهيئة', 'valeur' => 'en_renovation'],
                    ['nom_fr' => 'En réaménagement pédagogique', 'nom_ar' => 'في طور إعادة الهيكلة البيداغوجية', 'valeur' => 'reamenagement_pedagogique'],
                    ['nom_fr' => 'En attente d’habilitation', 'nom_ar' => 'في انتظار المصادقة', 'valeur' => 'attente_habilitation'],
                    ['nom_fr' => 'En reconversion', 'nom_ar' => 'في طور التحويل', 'valeur' => 'en_reconversion'],
                    ['nom_fr' => 'Fusionné avec un autre centre', 'nom_ar' => 'مدمج مع مركز آخر', 'valeur' => 'fusionne'],
                    ['nom_fr' => 'Transféré', 'nom_ar' => 'تم نقله', 'valeur' => 'transfere'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 19,
                'statut' => 'Actif',
                'description' => null,
            ],


            [
                'nom_fr' => 'Filières des personnels',
                'nom_ar' => 'مسالك الموظفين',
                'options' => [
                    ['nom_fr' => 'Filière des administratifs', 'nom_ar' => 'مسلك الاداريين', 'valeur' => 'administratifs'],
                    ['nom_fr' => 'Filière des agents de formation', 'nom_ar' => 'مسلك أعوان التكوين', 'valeur' => 'agents_formation'],
                    ['nom_fr' => 'Filière du cadre semi-pédagogique', 'nom_ar' => 'مسلك الإطار الشبه بيداغوجي', 'valeur' => 'cadre_semi_pedagogique'],
                    ['nom_fr' => 'Filière des techniciens', 'nom_ar' => 'مسلك الفنيين', 'valeur' => 'techniciens'],
                    ['nom_fr' => 'Filière des ouvriers', 'nom_ar' => 'مسلك العملة', 'valeur' => 'ouvriers'],
                ],
                'icone' => null,
                'couleur' => null,
                'fond' => null,
                'ordre' => 20,
                'statut' => 'Actif',
                'description' => null,
            ],




        ];

        // Insertion des données avec gestion des erreurs
        foreach ($listes as $index => $liste) {
            try {
                DB::table('listes')->insert([
                    'nom_fr' => $liste['nom_fr'],
                    'nom_ar' => $liste['nom_ar'],
                    'options' => json_encode($liste['options']),
                    'icone' => $liste['icone'],
                    'couleur' => $liste['couleur'],
                    'fond' => $liste['fond'],
                    'ordre' => $liste['ordre'],
                    'statut' => $liste['statut'],
                    'description' => $liste['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                $this->command->warn('Erreur insertion liste: '.$e->getMessage());

                continue;
            }
        }

        $this->command->info('Seeder exécuté avec succès!');
    }
}

// Commande terminal: php artisan db:seed --class=ListesSeeder
// Si tu veux exécuter tous les seeders, utilise : php artisan db:seed

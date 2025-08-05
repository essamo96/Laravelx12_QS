<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Validation Language Lines
      |--------------------------------------------------------------------------
      |
      | The following language lines contain the default error messages used by
      | the validator class. Some of these rules have multiple versions such
      | such as the size rules. Feel free to tweak each of these messages.
      |
     */

    'accepted' => 'يجب قبول :attribute',
    'active_url' => ':attribute لا يُمثّل رابطًا صحيحًا',
    'after' => 'يجب على :attribute أن يكون تاريخًا لاحقًا للتاريخ :date.',
    'after_or_equal' => ':attribute يجب أن يكون تاريخاً لاحقاً أو مطابقاً للتاريخ :date.',
    'alpha' => 'يجب أن لا يحتوي :attribute سوى على حروف',
    'alpha_dash' => 'يجب أن لا يحتوي :attribute على حروف، أرقام ومطّات.',
    'alpha_num' => 'يجب أن يحتوي :attribute على حروفٍ وأرقامٍ فقط',
    'array' => 'يجب أن يكون :attribute ًمصفوفة',
    'before' => 'يجب على :attribute أن يكون تاريخًا سابقًا للتاريخ :date.',
    'before_or_equal' => ':attribute يجب أن يكون تاريخا سابقا أو مطابقا للتاريخ :date',
    'between' => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون عدد حروف النّص :attribute بين :min و :max',
        'array' => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute إما true أو false ',
    'confirmed' => 'حقل التأكيد غير مُطابق للحقل :attribute',
    'date' => ':attribute ليس تاريخًا صحيحًا',
    'date_format' => 'لا يتوافق :attribute مع الشكل :format.',
    'different' => 'يجب أن يكون الحقلان :attribute و :other مُختلفان',
    'digits' => 'يجب أن يحتوي :attribute على :digits رقمًا/أرقام',
    'digits_between' => 'يجب أن يحتوي :attribute بين :min و :max رقمًا/أرقام ',
    'dimensions' => 'الـ :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'للحقل :attribute قيمة مُكرّرة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح البُنية',
    'exists' => ':attribute لاغٍ',
    'file' => 'الـ :attribute يجب أن يكون ملفا.',
    'filled' => ':attribute إجباري',
    'image' => 'يجب أن يكون :attribute صورةً',
    'in' => ':attribute لاغٍ',
    'in_array' => ':attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا',
    'ip' => 'يجب أن يكون :attribute عنوان IP صحيحًا',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صحيحًا.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صحيحًا.',
    'json' => 'يجب أن يكون :attribute نصآ من نوع JSON.',
    'max' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر لـ :max.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :max حروفٍ/حرفًا',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'mimes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'mimetypes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'min' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر لـ :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :min حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عُنصرًا/عناصر',
    ],
    'not_in' => ':attribute لاغٍ',
    'numeric' => 'يجب على :attribute أن يكون رقمًا',
    'present' => 'يجب تقديم :attribute',
    'regex' => 'صيغة :attribute .غير صحيحة',
    'required' => ':attribute مطلوب.',
    'required_if' => ':attribute مطلوب في حال ما إذا كان :other يساوي :value.',
    'required_unless' => ':attribute مطلوب في حال ما لم يكن :other يساوي :values.',
    'required_with' => ':attribute مطلوب إذا توفّر :values.',
    'required_with_all' => ':attribute مطلوب إذا توفّر :values.',
    'required_without' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'required_without_all' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'same' => 'يجب أن يتطابق :attribute مع :other',
    'size' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية لـ :size',
        'file' => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت',
        'string' => 'يجب أن يحتوي النص :attribute على :size حروفٍ/حرفًا بالظبط',
        'array' => 'يجب أن يحتوي :attribute على :size عنصرٍ/عناصر بالظبط',
    ],
    'string' => 'يجب أن يكون :attribute نصآ.',
    'timezone' => 'يجب أن يكون :attribute نطاقًا زمنيًا صحيحًا',
    'unique' => 'قيمة :attribute مُستخدمة من قبل',
    'uploaded' => 'فشل في تحميل الـ :attribute',
    'url' => 'صيغة الرابط :attribute غير صحيحة',
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Language Lines
      |--------------------------------------------------------------------------
      |
      | Here you may specify custom validation messages for attributes using the
      | convention "attribute.rule" to name the lines. This makes it quick to
      | specify a specific custom language line for a given attribute rule.
      |
     */
    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'first_name_ar' => [
            'required' => '  الاسم الأول (بالعربية) مطلوب.',
            'regex' => '  الاسم الأول (بالعربية) يجب أن يحتوي على أحرف عربية فقط.',
        ],
        'last_name_ar' => [
            'required' => '  الاسم الأخير (بالعربية) مطلوب.',
            'regex' => '  الاسم الأخير (بالعربية) يجب أن يحتوي على أحرف عربية فقط.',
        ],
        'father_name_ar' => [
            'regex' => '  اسم الأب (بالعربية) يجب أن يحتوي على أحرف عربية فقط.',
        ],
        'grandfather_name_ar' => [
            'regex' => '  اسم الجد (بالعربية) يجب أن يحتوي على أحرف عربية فقط.',
        ],
        'first_name_en' => [
            'required' => '  الاسم الأول (بالإنجليزية) مطلوب.',
            'regex' => '  الاسم الأول (بالإنجليزية) يجب أن يحتوي على أحرف إنجليزية فقط.',
        ],
        'last_name_en' => [
            // 'required' => '  الاسم الأخير (بالإنجليزية) مطلوب.',
            'regex' => '  الاسم الأخير (بالإنجليزية) يجب أن يحتوي على أحرف إنجليزية فقط.',
        ],
        'qid' => [
            'required' => '  الهوية الوطنية مطلوب.',
            'regex' => 'صيغة   الهوية الوطنية غير صحيحة.',
        ],
        'identity_type_id' => [
            'required' => '  نوع الهوية مطلوب.',
        ],
        'role_id' => [
            'required' => '  المجمووعة مطلوب.',
            'not_in' => ' يجب اختيار المجموعة',
        ],

        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'region_no' => [
            'required' => '  رقم المنطقة مطلوب.',
            'numeric' => '  رقم المنطقة يجب أن يكون قيمة رقمية.',
        ],
        'street_no' => [
            'required' => '  رقم الشارع مطلوب.',
            'numeric' => '  رقم الشارع يجب أن يكون قيمة رقمية.',
        ],
        'building_no' => [
            'required' => '  رقم المبنى مطلوب.',
            'numeric' => '  رقم المبنى يجب أن يكون قيمة رقمية.',
        ],
        'unit_no' => [
            'numeric' => '  رقم الوحدة يجب أن يكون قيمة رقمية.',
        ],
        'kt_docs_repeater_advanced' => [
            'nullable' => '  الكرر المتقدم (kt_docs_repeater_advanced) يجب أن يكون فارغًا أو مصفوفة.',
            'array' => '  الكرر المتقدم (kt_docs_repeater_advanced) يجب أن يكون مصفوفة.',
        ],
        'kt_docs_repeater_advanced.*.attachmen_id' => [
            'required' => '   المرفق (attachmen_id) مطلوب.',
            'integer' => '   المرفق (attachmen_id) يجب أن يكون قيمة صحيحة.',
        ],
        'kt_docs_repeater_advanced.*.Issue_date' => [
            'nullable' => '  تاريخ الإصدار (Issue_date) يجب أن يكون فارغًا أو تاريخًا.',
            'date' => '  تاريخ الإصدار (Issue_date) يجب أن يكون تاريخًا صحيحًا.',
        ],
        'kt_docs_repeater_advanced.*.expiry_date' => [
            'nullable' => '  تاريخ الانتهاء (expiry_date) يجب أن يكون فارغًا أو تاريخًا.',
            'date' => '  تاريخ الانتهاء (expiry_date) يجب أن يكون تاريخًا صحيحًا.',
        ],
        'kt_docs_repeater_advanced.*.attach_no' => [
            'nullable' => '  رقم المرفق (attach_no) يجب أن يكون فارغًا أو نصًا.',
            'string' => '  رقم المرفق (attach_no) يجب أن يكون نصًا.',
        ],
        'kt_docs_repeater_advanced.*.attachments' => [
            'required' => '  المرفقات (attachments) مطلوب.',
            'file' => '  المرفقات (attachments) يجب أن يكون ملفًا.',
            'mimetypes' => '  المرفقات (attachments) يجب أن يكون ملفًا من نوع صورة (PNG أو JPEG) أو ملف PDF.',
        ],

        'employee_id' => [
            'required' => '  اسم الموظف مطلوب.',
            'numeric' => '  اسم الموظف يجب أن يكون رقميًا.',
        ],
        'kt_docs_repeater_advanced' => [
            'required' => '  الوثائق المتقدمة مطلوب.',
            'array' => '  الوثائق المتقدمة يجب أن يكون مصفوفة.',
            'min' => '  الوثائق المتقدمة يجب أن يحتوي على عنصر واحد على الأقل.',
        ],

        'license_number' => [
            'nullable' => '  رقم الترخيص يمكن أن يكون فارغًا.',
            'required_if' => '  رقم الترخيص مطلوب عندما يكون للوظيفة ترخيص.',
            'regex' => 'يجب أن يحتوي رقم الترخيص على أحرف إنجليزية وأرقام فقط.',
        ],
        'issue_date' => [
            'nullable' => '  تاريخ الإصدار يمكن أن يكون فارغًا.',
            'required_if' => '  تاريخ الإصدار مطلوب عندما يكون للوظيفة ترخيص.',
        ],
        'license_expiry_date' => [
            'nullable' => '  تاريخ انتهاء الترخيص يمكن أن يكون فارغًا.',
            'required_if' => 'حقل تاريخ انتهاء الترخيص مطلوب عندما يكون للوظيفة ترخيص.',
        ],

        'qid_expiry_date.required' => '  تاريخ انتهاء بطاقة الهوية مطلوب.',
        'gender.required' => '  الجنس مطلوب.',
        'nationality_id.required' => '  الجنسية مطلوب.',
        'situation_id.required' => '  الحالة الاجتماعية مطلوب.',
        'name_emergency.required' => '  اسم شخص للطوارئ  مطلوب.',
        'dob.required' => '  تاريخ الميلاد مطلوب.',
        'emergency_mobile.required' => '  رقم هاتف الطوارئ  مطلوب.',
        'email.required' => '  البريد الإلكتروني مطلوب.',
        'email.email' => '  البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح.',
        'email.unique' => 'عنوان البريد الإلكتروني مستخدم بالفعل.',
        'email.regex' => 'صيغة   البريد الإلكتروني غير صالحة.',
        'status.required_if' => '  الحالة مطلوب عندما يكون الحالة نشطة.',
        'status.numeric' => '  الحالة يجب أن يكون رقمًا.',
        'status.in' => '  الحالة يجب أن يكون إما 0 أو 1.',
        'mobile.required' => '  رقم هاتف الطوارئ مطلوب.',
        'mobile.digits' => '  رقم هاتف الطوارئ يجب أن يحتوي على 8 أرقام.',
        'telephone.digits' => '  الهاتف يجب أن يحتوي على 8 أرقام.',
        'emergency_mobile.digits' => '  رقم هاتف الطوارئ للطوارئ يجب أن يحتوي على 8 أرقام.',
       
        'required' => '  :attribute مطلوب.',
        'job_id.required' => '  الرقم الوظيفي مطلوب.',
        'kt_allowance_repeater_advanced.nullable' => '  التعويضات غير مطلوب إذا كان فارغًا.',
        'kt_allowance_repeater_advanced.*.allowance_id.required' => '   التعويض مطلوب.',
        'kt_allowance_repeater_advanced.*.allowance_id.integer' => '  معرّف التعويض يجب أن يكون عددًا صحيحًا.',

    ],
    
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Attributes
      |--------------------------------------------------------------------------
      |
      | The following language lines are used to swap attribute place-holders
      | with something more reader friendly such as E-Mail Address instead
      | of "email". This simply helps us make messages a little cleaner.
      |
     */
    'attributes' => [
        'username' => 'اسم المُستخدم',
        'name' => 'الاسم',
        'license_number' => 'رقم الترخيص',
        'issue_date' => 'تاريخ الاصدار',
        'license_expiry_date' => 'تاريخ انتهاء الترخيص',
        'email' => 'البريد الالكتروني',
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'password' => 'كلمة السر',
        'password_confirmation' => 'تأكيد كلمة السر',
        'city' => 'المدينة',
        'country' => 'الدولة',
        'address' => 'عنوان السكن',
        'phone' => 'الهاتف',
        'mobile' => 'الجوال',
        'age' => 'العمر',
        'sex' => 'الجنس',
        'incentive_date' => 'تاريخ الحافز او الخصم',
        'gender' => 'النوع',
        'day' => 'اليوم',
        'month' => 'الشهر',
        'year' => 'السنة',
        'hour' => 'ساعة',
        'minute' => 'دقيقة',
        'second' => 'ثانية',
        'title' => 'العنوان',
        'content' => 'المُحتوى',
        'description' => 'الوصف',
        'details' => 'ملاحظات اضافية',
        'company' => 'الشركة',
        'excerpt' => 'المُلخص',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'available' => 'مُتاح',
        'size' => 'الحجم',
        'size' => 'الحجم',
        'company_name' => 'اسم الشركة',
        'company_reg_no' => 'رقم تسجيل الشركة',
        'company_tax_no' => 'رقم الضريبي الشركة',
        'company_person' => 'اسم شخض للتواصل',
        'emp_postion' => 'المسمي الوظيفي',
        'emp_tel' => 'رقم هاتف',
        'emp_mobile' => 'رقم موبيل',
        'new_grope' => 'المجموعة',
        'first_name_ar' => 'الاسم الاول (AR)',
        'father_name_ar' => 'اسم الاب (AR)',
        'grandfather_name_ar' => 'اسم الجد (AR)',
        'last_name_ar' => 'اسم العائلة (AR)',
        'first_name_en' => 'الاسم الاول(En)',
        'last_name_en' => 'اسم العائلة (EN)',
        'father_name_en' => 'اسم الاب (EN)',
        'grandfather_name_en' => 'اسم الجد (EN)',
        'identity_type_id' => 'نوع الهوية',
        'qid' => 'الرقم الهوية المحددة ',
        'qid_expiry_date' => 'تاريخ انتهاء الهوية  ',
        'gender' => 'الجنس ',
        'dob' => 'تاريخ الميلاد ',
        'situation_id' => 'الحالة الاجتماعية ',
        'nationality_id' => ' الجنسية ',
        'religion_id' => ' الديانة ',
        'email' => ' الايميل ',
        'vacation' => ' نوع الاجازة ',
        'mobile' => ' رقم الجوال ',
        'telephone' => ' رقم الهاتف ',
        'name_emergency' => 'اسم شخص للطوارئ ',
        'emergency_mobile' => 'رقم هاتف الطوارئ',
        'role_id' => 'المجموعة',

        'job_id' => 'الرقم الوظيفي',
        'occupation' => 'الوظيفة',
        'specialties_id' => 'التخصص',
        'qualification_id' => 'المؤهل العلمي',
        'section_id' => 'القسم',
        'vacation_leave' => 'الاجازات السنوية',
        'number_years' => 'سنوات الخبرة ',
        'contract_date_id' => ' مدة العقد ',
        'date_join' => 'تاريخ التعيين',
        'main_salary' => '  الراتب الأساسي ',
        'ticket_value' => '  قيمة التذكرة ',
        'ticket_types_id' => '   نوع التذكرة ',
        'eligibility_tickets' => 'استحقاق تذاكر السفر',
        'banks_id' => ' اسم البنك',
        'IBAN' => 'حساب البنكي(IBAN) ',
        'kt_allowance_repeater_advanced.*.allowance_value' => 'قيمة البدل ',
        'allowance_id' => 'نوع البدل ',
        'kt_docs_repeater_advanced.*.name_ar' => 'الاسم كامل(AR) ',
        'kt_docs_repeater_advanced.*.name_en' => 'الاسم كامل(ER) ',
        'kt_docs_repeater_advanced.*.relationship_id' => 'العلاقة',
        'kt_docs_repeater_advanced.*.id_number' => 'رقم البطاقة',
        'kt_docs_repeater_advanced.*.gender' => 'الجنس',
        'kt_docs_repeater_advanced.*.dob' => 'تاريخ الميلاد',
        'kt_docs_repeater_advanced.*.Identity_expiry_date' => '  تاريخ انتهاء البطاقة',
        'kt_docs_repeater_advanced.*.id_card_image' => ' مرفق البطاقة الشخصية',
        'kt_docs_repeater_advanced.*.health_card_image' => '   مرفق البطاقة الصحية',
        'kt_docs_repeater_advanced.*.health_insurance_image' => 'مرفق التأمين الصحي',
        
        'name_ar' => ' الاسم بالغة العربية ',
        'name_en' => ' الاسم بالغة الانجليزية ',
        'amount' => 'القيمة',
        'start_date' => 'تاريخ البدء',
        'end_date' => 'تاريخ الانتهاء',
        'dayes' => 'عدد الايام',
  
    ],
];

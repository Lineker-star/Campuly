<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (French)
    |--------------------------------------------------------------------------
    |
    | Same keys as lang/en/validation.php. Rules missing here fall back to
    | English (APP_FALLBACK_LOCALE). Field names are in "attributes" below.
    |
    */

    'accepted' => 'Le champ :attribute doit être accepté.',
    'accepted_if' => 'Le champ :attribute doit être accepté quand :other vaut :value.',
    'active_url' => "Le champ :attribute n'est pas une URL valide.",
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne doit contenir que des lettres, des chiffres, des tirets et des traits de soulignement.',
    'alpha_num' => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être une liste.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between' => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'file' => 'Le fichier :attribute doit peser entre :min et :max kilo-octets.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => "Le champ :attribute n'est pas une date valide.",
    'date_equals' => 'Le champ :attribute doit être une date égale au :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions' => "Les dimensions de l'image :attribute ne sont pas valides.",
    'distinct' => 'Le champ :attribute contient une valeur en double.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with' => "Le champ :attribute doit se terminer par l'une des valeurs suivantes\u{00A0}: :values.",
    'exists' => "La valeur choisie pour :attribute n'est pas valide.",
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'file' => 'Le fichier :attribute doit peser plus de :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
    ],
    'gte' => [
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'file' => 'Le fichier :attribute doit peser au moins :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
        'array' => 'Le champ :attribute doit contenir au moins :value éléments.',
    ],
    'image' => 'Le champ :attribute doit être une image.',
    'in' => "La valeur choisie pour :attribute n'est pas valide.",
    'in_array' => "Le champ :attribute n'existe pas dans :other.",
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4' => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json' => 'Le champ :attribute doit être un texte JSON valide.',
    'lt' => [
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'file' => 'Le fichier :attribute doit peser moins de :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
    ],
    'lte' => [
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'file' => 'Le fichier :attribute ne doit pas peser plus de :value kilo-octets.',
        'string' => 'Le champ :attribute ne doit pas contenir plus de :value caractères.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
    ],
    'max' => [
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
    ],
    'mimes' => "Le champ :attribute doit être un fichier de type\u{00A0}: :values.",
    'mimetypes' => "Le champ :attribute doit être un fichier de type\u{00A0}: :values.",
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'file' => 'Le fichier :attribute doit peser au moins :min kilo-octets.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
    ],
    'multiple_of' => 'Le champ :attribute doit être un multiple de :value.',
    'not_in' => "La valeur choisie pour :attribute n'est pas valide.",
    'not_regex' => "Le format du champ :attribute n'est pas valide.",
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => 'Le mot de passe est incorrect.',
    'present' => 'Le champ :attribute doit être présent.',
    'regex' => "Le format du champ :attribute n'est pas valide.",
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other fait partie de :values.',
    'required_with' => 'Le champ :attribute est obligatoire quand :values est renseigné.',
    'required_with_all' => 'Le champ :attribute est obligatoire quand :values sont renseignés.',
    'required_without' => "Le champ :attribute est obligatoire quand :values n'est pas renseigné.",
    'required_without_all' => "Le champ :attribute est obligatoire quand aucun des champs :values n'est renseigné.",
    'prohibited' => 'Le champ :attribute est interdit.',
    'prohibited_if' => 'Le champ :attribute est interdit quand :other vaut :value.',
    'prohibited_unless' => 'Le champ :attribute est interdit sauf si :other fait partie de :values.',
    'prohibits' => 'Le champ :attribute interdit la présence de :other.',
    'same' => 'Les champs :attribute et :other doivent correspondre.',
    'size' => [
        'numeric' => 'Le champ :attribute doit valoir :size.',
        'file' => 'Le fichier :attribute doit peser :size kilo-octets.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
        'array' => 'Le champ :attribute doit contenir :size éléments.',
    ],
    'starts_with' => "Le champ :attribute doit commencer par l'une des valeurs suivantes\u{00A0}: :values.",
    'string' => 'Le champ :attribute doit être un texte.',
    'timezone' => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique' => 'La valeur du champ :attribute est déjà utilisée.',
    'uploaded' => 'Le téléversement du fichier :attribute a échoué.',
    'url' => 'Le champ :attribute doit être une URL valide.',
    'uuid' => 'Le champ :attribute doit être un UUID valide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Field names (French)
    |--------------------------------------------------------------------------
    |
    | Each translated module adds the names of its own fields here.
    |
    */

    'attributes' => [
        'name' => 'nom',
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'current_password' => 'mot de passe actuel',
        'phone' => 'numéro de téléphone',
        'address' => 'adresse',
        'address_line_2' => "ligne d'adresse 2",
        'city' => 'ville',
        'state' => 'région',
        'country' => 'pays',
        'postal_code' => 'code postal',
        'birthday' => 'date de naissance',
        'gender' => 'sexe',
        'nationality' => 'nationalité',
        'photo' => 'photo',
        'locale' => 'langue',
        'code' => 'code',
        'recovery_code' => 'code de récupération',
        'token' => 'jeton',
        'admin_name' => "nom de l'administrateur",
        'admin_email' => "adresse e-mail de l'administrateur",
        'admin_password' => "mot de passe de l'administrateur",
        'organization_name' => "nom de l'organisation",
        'campus_name' => 'nom du campus',
        'campus_address' => 'adresse du campus',
        'campus_country' => 'pays du campus',
        'campus_state' => 'région du campus',
        'campus_city' => 'ville du campus',
        'campus_postal_code' => 'code postal du campus',
        'campus_initials' => 'initiales du campus',
        'campus_email' => 'adresse e-mail du campus',
        'title' => 'titre',
        'content' => 'contenu',
        'description' => 'description',
        'start_date' => 'date de début',
        'stop_date' => 'date de fin',
        'date' => 'date',
        'amount' => 'montant',
        'reference' => 'référence',
    ],

];

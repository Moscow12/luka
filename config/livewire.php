<?php

// Only the keys overridden here; everything else uses Livewire's defaults.
return [
    'temporary_file_upload' => [
        'disk' => null,
        // 25MB (ticket attachments). Every type any form accepts, so scripts, HTML and SVG
        // are refused before they even reach the temporary upload folder.
        'rules' => ['required', 'file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,ppt,pptx,txt,csv'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'wav', 'mp4', // svg removed: it can carry script
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],
];

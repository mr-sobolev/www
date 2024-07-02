<?php

/*
      This array to validate incoming input data on Controller
*/
return [
      'message' => [
        'rules' => [
            'title' => 'required|string|max:100',
            'content' => 'required|string',
            'is_done' => 'boolean',
        ],
      ],
];

?>
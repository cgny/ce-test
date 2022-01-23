<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 10:49 AM
 */

return [
    'POST' => [
        'company'      => [ 'class' => 'models\Data', 'function' => 'getCompanyData', 'args' => 'ticker', 'required' => ['ticker'] ],  // by symbol
        'allCompanies' => [ 'class' => 'models\Data', 'function' => 'getAllCompanies', 'args' => '', 'required' => [] ],  ///
        'history'      => [ 'class' => 'models\Data', 'function' => 'getCompanyHistory', 'args' => 'ticker,date_from,date_to', 'required' => ['ticker'] ],
        'highLow'      => [ 'class' => 'models\Data', 'function' => 'getCompanyHighLow', 'args' => 'ticker,date_from,date_to', 'required' => ['ticker'] ], //time frame
        'supRes'       => [ 'class' => 'models\Data', 'function' => 'getCompanySupportResistanceAVG', 'args' => 'ticker,date_from,date_to', 'required' => ['ticker'] ], //time frame
    ],
    'GET'  => [
        'endPoints' => [ 'class' => 'api\Api', 'function' => 'loadEndPoints', 'args' => '', 'required' => [] ],
    ]
];
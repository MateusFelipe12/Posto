<?php
    
    // se for uma ação, manda pro model tratar
    if(isset($_GET['action'])){
        require('./Model/UnitMeasure.php');
    }
    // se não o view que lute
    require('./View/unitMeasureView.php');
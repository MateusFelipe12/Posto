<?php
    
    if(isset($_GET['action'])){
       require('./Model/SupplyModel.php');
    }
    require('./View/supplyView.php');
?>
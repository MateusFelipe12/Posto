<?php
    
    if(isset($_GET['action'])){
        require('./Model/ProductModel.php');
    }

    require('./View/productView.php');
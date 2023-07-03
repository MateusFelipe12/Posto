<?php
    require('./Model/config.php');

    if(isset($_GET['action'])){
      require('./Model/ProviderModel.php');
    }
    
    
    require('./View/providerView.php');
<?php
namespace Controllers;

use Views\Company\IndexView;

class Landing {
    
    public function main() {
        // Usar la clase de vista en lugar de require_once
        IndexView::render();
    }
}
?>
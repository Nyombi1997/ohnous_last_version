<?php

class CheckoutController
{
    public function showCheckout()
    {
        $view = new View('checkout');
        $view->render('Ohnous | Checkout');
    }
}

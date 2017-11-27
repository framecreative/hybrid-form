<?php if ( !function_exists( 'add_action' ) ) {exit;}

function hyb_flash_message( $name, $message = '', $class = 'alert alert-error' ) {
    if(!session_id()) {
        @session_start();
    }

    //No message, create it
    if(!empty( $message )){
        if( !empty( $_SESSION[$name] ) ) {
            unset( $_SESSION[$name] );
        }
        if( !empty( $_SESSION[$name.'_class'] ) ){
            unset( $_SESSION[$name.'_class'] );
        }

        $_SESSION[$name] = $message;
        $_SESSION[$name.'_class'] = $class;
    }
    //Message exists, display it
    elseif(!empty( $_SESSION[$name] ) && empty( $message )) {
        $class = !empty( $_SESSION[$name.'_class'] ) ? $_SESSION[$name.'_class'] : 'alert alert-error';
        foreach(explode("\r\n", trim($_SESSION[$name])) as $line) {
            echo '<div class="'.$class.'">'.$line.'</div>';
        }
        unset($_SESSION[$name]);
        unset($_SESSION[$name.'_class']);
    }
}
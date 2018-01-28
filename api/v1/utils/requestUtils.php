<?php

class requestUtils {

    public static function getAppHome($request){
        $apiBase = $request->getUri()->getBaseUrl();
        $last = strrpos($apiBase, '/');
        $next_to_last = strrpos($apiBase, '/', $last - strlen($apiBase) - 1);
        return substr($apiBase, 0, $next_to_last + 1);
    }
}

?>

<?php
class PuzzleUserRecaptcha
{
    public static function verify()
    {
        $result = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify',
            false,
            stream_context_create([
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query([
                        'secret' => PuzzleUserConfig::recaptchaPrivatekey(),
                        'response' => $_POST["g-recaptcha-response"],
                        'remoteip' => $_SERVER["REMOTE_ADDR"]
                    ])
                ]
            ])
        );
        if ($result === false) throw new PuzzleError("Cannot contact Google for Recaptcha");
        return (json_decode($result)->success);
    }
}

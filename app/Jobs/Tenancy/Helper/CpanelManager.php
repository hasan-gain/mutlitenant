<?php
namespace App\Jobs\Tenancy\Helper;


class CpanelManager
{
    private string $domain;
    private string $cPanelUser;
    private string $cPanelPassword;
    private string $cPanelPort;

    public function __construct($domain, $cPanelUser, $cPanelPassword, $cpanelPort = '2083')
    {
        $this->domain = $domain;
        $this->cPanelUser = $cPanelUser;
        $this->cPanelPassword = $cPanelPassword;
        $this->cPanelPort = $cpanelPort;
    }

    /////////////// MYSQL CPANEL //////////////////

    public function createDataBaseMySQL($database)
    {

        $func = "https://$this->domain:$this->cPanelPort/execute/Mysql/create_database?name=$database";
        return $this->executeCpanel($func);
    }

    public function createUserMySQL($user, $password)
    {
        $func = "https://$this->domain:$this->cPanelPort/execute/Mysql/create_user?name=$user&password=$password";
        return $this->executeCpanel($func);
    }

    // All access permission
    public function setPrivilegesMySQL($user, $database)
    {
        $func = "https://$this->domain:$this->cPanelPort/execute/Mysql/set_privileges_on_database?user=$user&database=$database&privileges=ALL";
        return $this->executeCpanel($func);

    }

    private function executeCpanel($func = '')
    {
        $query = $func;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $header[0] = "Authorization: Basic " . base64_encode($this->cPanelUser . ":" . $this->cPanelPassword) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $query);
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
        }
        curl_close($curl);
        return $result;
    }
}

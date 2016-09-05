<?php
    require_once('SessionManager.php');
?>

<?php
    require_once('Credentials.php');

    //Esta classe realiza as chamadas de API no site do deviantART e retorna o seu resultado.
    class DeviantQuery
    {
        function DeviantQuery()
        {
            $this->client_id = Credentials::App()['CLIENT_ID'];
            $this->client_secret = Credentials::App()['CLIENT_SECRET'];
        }
        
        public function newest($category_path, $q, $offset, $limit)
        {
            $this->verifyAccessToken();
            
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://www.deviantart.com/api/v1/oauth2/browse/newest',
                CURLOPT_POST => 1,
                CURLOPT_USERAGENT => $_SERVER ['HTTP_USER_AGENT'],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
                CURLOPT_POSTFIELDS => array(
                    'category_path' => $category_path,
                    'q' => $q,
                    'offset' => $offset,
                    'limit' => $limit,
                    'access_token' => $this->AT
                    )
                ));
        
            $resp = curl_exec($curl);
            
            if($resp)
            {
                $json = json_decode($resp);
                return $json->access_token;
            }
            else
            {
                die('Error: "' . curl_error($curl) . '" - Code: ' . curl_error($curl));
            }
            
            curl_close($curl);
        }
        
        public function verifyAccessToken()
        {
            //Get the difference between STARTED and LAST_ACTIVITY in seconds on INT variable:
            $interval = SessionManager::getAccessTokenTime()->diff(new DateTime('NOW'));
            $diff = (new DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
            echo 'diff: '.$diff.'<br>';
            
            if($diff < 3600)
            {
                echo 'ainda não deu tempo!<br>';
            }
            else
            {
                echo 'deu tempo!<br>';
                $this->generateAT();
                SessionManager::updateAccessToken($this->AT);
            }
        }
        
        public function getAT()
        {
            return $this->AT;
        }
        
        private function generateAT()
        {
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://www.deviantart.com/oauth2/token',
                CURLOPT_POST => 1,
                CURLOPT_USERAGENT => $_SERVER ['HTTP_USER_AGENT'],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
                CURLOPT_POSTFIELDS => array(
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret
                    )
                ));
        
            $resp = curl_exec($curl);
            
            if($resp)
            {
                $json = json_decode($resp);
                $this->AT = $json->access_token;
            }
            else
            {
                die('Error: "' . curl_error($curl) . '" - Code: ' . curl_error($curl));
            }
            
            curl_close($curl);
        }
        
        private $conn;
        private $client_id;
        private $client_secret;
        
        private $AT;
    }



?>
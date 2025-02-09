<?php

// php .\collector.php --user=YOUR_USER_NAME --token=YOUR_TOKEN

function println($value)
{
    print($value . PHP_EOL);
}

println("Welcome to Github projects Collector!");
$options = getopt("", array("user:", "token:"));
println("Got username: " . $options["user"]);
println("Got token: " . $options["token"]);

$username = $options["user"];
$token = $options["token"];

class GithubClient
{

    private string $username;
    private HttpClient $client;

    public function __construct($username, $accessToken)
    {
        $this->username = $username;
        $this->client = new HttpClient($accessToken);
    }

    public function getAllRepos(): array
    {
        return $this->requestForPublicRepositories();
    }

    public function downloadAllRepos($directory)
    {
        mkdir($directory);
        $allRepositories = $this->getAllRepos();
        // print_r($allRepositories);
        foreach ($allRepositories as $item) {
            $repoName = $item['name'];
            println(sprintf("Saving %s", $repoName));
            $bytes = $this->downloadRepo($repoName);
            file_put_contents($directory . DIRECTORY_SEPARATOR . $repoName . '.tar.gz', $bytes);
        }
        println("Done.");
    }

    private function requestForPublicRepositories(): array
    {
        $allPagesContent = [];
        $pageIndex = 0;
        while ($page = $this->requestForPage($pageIndex)) {
            $allPagesContent += array_merge($allPagesContent, $page);
            $pageIndex += 1;
        }
        return $allPagesContent;
    }


    private function requestForPage(int $page): array
    {
        $currentPage = json_decode($this->get("https://api.github.com/user/repos?per_page=100&page=$page&visibility=all"), true);
        return $currentPage;
    }

    private function downloadRepo($repoName): string
    {
        return $this->get("https://api.github.com/repos/$this->username/$repoName/tarball");
    }


    private function get($url): string
    {
        return $this->client->get($url);
    }
}


class HttpClient
{
    private string $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function get($url): string
    {
        return $this->request($url, "GET");
    }

    private function request($url, $method): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Curl",
            "Authorization: Bearer $this->accessToken",
        ]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (curl_errno($ch)) {
            print curl_error($ch);
        }

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}



$client = new GithubClient($username, $token);
$client->downloadAllRepos($username . DIRECTORY_SEPARATOR . date("Y-m-d_H-i-s"));
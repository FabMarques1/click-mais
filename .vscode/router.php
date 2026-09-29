<?php
 /* Hello dev {-_-}. Please not modify this file. Thank you. */
if (php_sapi_name() == "cli-server") {
     class MyRouter extends \stdClass { 
        public function __construct()
        {
            $this->query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (substr($this->path, -1) === "/") $this->path = substr($this->path, 0, -1);
            if (strlen($this->path) > 0 ) $this->path = "$this->path";
            else if (empty($this->path)) $this->path = "/";
            if ($this->path === "/") $this->path = "/index.php";
        }
    };
    $req = new MyRouter();
    $pathinfo = (object) pathinfo($req->path);
    if (property_exists($pathinfo, "extension")) {
        if (array_search(strtolower($pathinfo->extension), ["php", "html", "htm"]) !== false) {
            if (strtolower($_SERVER['REQUEST_URI']) === "/.vscode/router.php") die;
            ob_start();
 ?>
            <script src="https://cdn.socket.io/4.7.5/socket.io.min.js" integrity="sha384-2huaZvOR9iDzHqslqwpR87isEmrfxqyWOF7hr7BY6KG0+hVKLoEXMPUJw3ynWuhO" crossorigin="anonymous"></script>
            <script type="module">
                window.addEventListener('DOMContentLoaded', (event) => {
                const socket = io(`ws://localhost:5003`, { 
                    auth: { token : "M2JjMDZiODQtNmRlZS00NGMzLTgxMTgtMTJkYzAyOWUxODdmMTc5MDM3Mjg0OTc0OQ==aMc" },   
                    withCredentials: true,
                    reconnectionAttempts: 3
                });
                socket.on("connect", () => { console.log(`Connect_success : _${ socket.id }_live _php.`); });
                socket.on("message", data => {
                    const d =  data.toLowerCase() || "";
                    if(!d || d.length === 0) return;
                    if(d === 'refresh') { window.location.reload(true); return ;};
                    if(d.includes("/") && d.endsWith(".php")){
                       if(d === "/index.php"){ window.location.href = "/"; return;}
                       else { window.location.href = d || "/"; return ;};
                    }
                })
                socket.on("disconnect", (reason) => {  console.log("Disconnected from server"); });
                }); 
            </script>
        <?php  ob_get_flush(); }
    }  
    include(str_replace("/", DIRECTORY_SEPARATOR,  __DIR__ . "/.." . $req->path));
}
?>
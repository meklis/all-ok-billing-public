server {
        listen 80;
        listen [::]:80;
        root /www/pinger;
        index index.php;

#       listen 443 ssl;
#       ssl_certificate     /www/files/certs/ssl/bundled.crt;
#       ssl_certificate_key /www/files/certs/ssl/private.key;
#       ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
#       ssl_ciphers         HIGH:!aNULL:!MD5;
#       if ($ssl_protocol = "") {rewrite ^ https://$server_name$request_uri? permanent;}

        access_log /var/log/nginx/all-ok-billing.sw.access.log;
        error_log  /var/log/nginx/all-ok-billing.sw.error.log;
                                                        
        server_name sw.*;

        location / {
           proxy_pass http://127.0.0.1:8088;
           proxy_set_header X-Real-Ip $remote_addr;
           proxy_set_header X-Forwarder-For $proxy_add_x_forwarded_for;
        }
}

                 

# Buscas em Tribunais
## Requisitos

- Chrome(versão 77) ou Chormium

## Atualizando a aplicação

- Entrar na `<pasta-onde-o-site-foi-instalado>`
- Baixar as atualizações de código fonte usando Git (git pull ou git fetch + git merge, isso depende de como operador prefere trabalhar com Git)
- Executar, no mínimo, os comandos:

```
composer install
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan dusk:chrome-driver
```

## Execução

- Agendar a execução de tempos em tempos do comando `php artisan scrape` a ser executado dentro de `<pasta-onde-o-site-foi-instalado>`
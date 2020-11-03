Symfony + Doctrine + Mysql


1. Pobrać pliki projektu.
2. Przejść do folderu i wykonać polecenie: 'composer install'
3. w pliku .env znajduje się konfiguracja bazy mysql: DATABASE_URL=mysql://test:test@127.0.0.1:3306/test 
    username = test, password = test, database = test
4. odpalić symfony local web server za pomocą polecenia 'symfony server:start' (bez zainstalowanego symfony moze nie zadziałać)
    api będzie dostępne prawdopodobnie pod adresem https://127.0.0.1:8000/ jeśli nie to adres wyświętla się w konsoli po odpaleniu SymfonyLWS

https://app.swaggerhub.com/apis-docs/beesfund8/backend-recruitment/1.0

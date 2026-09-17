{
  description = "PHP 8.4 Development Environment with Xdebug, Pcov, and Composer";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs/nixos-unstable";
  };

  outputs =
    { self, nixpkgs }:
    let
      supportedSystems = [
        "x86_64-linux"
        "aarch64-linux"
        "x86_64-darwin"
        "aarch64-darwin"
      ];
      forEachSystem = f: nixpkgs.lib.genAttrs supportedSystems (system: f system);
    in
    {
      devShells = forEachSystem (
        system:
        let
          pkgs = import nixpkgs { inherit system; };

          # buildEnv (not withExtensions!) both compiles the extensions AND
          # generates a php.ini that sets extension_dir + enables them.
          # withExtensions alone leaves extension_dir unset, which is why
          # `-d extension=xdebug.so` couldn't find the .so file.
          phpEnv = pkgs.php84.buildEnv {
            extensions = { all, ... }: with all;
              [
                pcov
                tokenizer
                xmlwriter
                xdebug
                opcache
                mbstring
                openssl
                curl
                dom
                pdo
                pdo_sqlite
                pdo_pgsql
                ctype
                fileinfo
                bcmath
                filter
                session
                zlib

              ];

            extraConfig = ''
              ; Both extensions are loaded at startup now -- no need to
              ; re-load them with -d extension=... on the command line.
              ; Just toggle their behavior per-invocation instead.
            '';
          };
        in
        {
          default = pkgs.mkShell {
            buildInputs = [
              phpEnv
              pkgs.sqlite
              pkgs.php84Packages.composer # Composer coupled directly to the PHP 8.4 engine
              pkgs.phpactor
            ];

            shellHook = ''
              # Toggle MODE, not extension loading -- both .so files are
              # already compiled in and enabled via phpEnv's generated ini.
              alias php-pcov="php -d pcov.enabled=1 -d xdebug.mode=off"
              alias php-xdebug="XDEBUG_MODE=debug XDEBUG_SESSION=1 php -d xdebug.client_port=9003 -d xdebug.start_with_request=yes -d pcov.enabled=0"
              echo "🐘 Nix PHP 8.4 Flake Activated Successfully!"
              echo "⚙️  Version: $(php -r 'echo PHP_VERSION;')"
              echo "💡 Run 'php-pcov' for test coverage pipelines or 'php-xdebug' for step-debugging."
            '';
          };
        }
      );
    };
}

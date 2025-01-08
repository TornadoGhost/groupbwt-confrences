# Conference's registry on Symfony

A project where users can participate in or listen to a conference.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up -d` to set up and start a project
4. Copy .env.dist and rename it to .env
5. Run `docker compose exec php bin/console secrets:generate-keys` to generate App key
6. Run `docker compose exec php bin/console lexik:jwt:generate-keypair` to generate SSL keys for JWT
7. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
8. Run `docker compose down --remove-orphans` to stop the Docker containers.

## DataBase Diagram
[Link to diagram](https://dbdiagram.io/d/Conferences-675b14c946c15ed47932b533)

## Google Map
For Google Map to work you need Google Map Api Key, you can create your own or ask me to send my key.

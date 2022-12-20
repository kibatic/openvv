OpenVV : open virtual visit
==========================

This goal of the project is to help to create virtual visits with panorama 360.

This project uses the excellent [Photo Sphere Viewer](https://photo-sphere-viewer.js.org/).

Features
--------

* Display 3 types of visits
  * Create gallery
  * Create virtual visit
  * Create single panorama
* Register an account
* create projects, with several media
* create links between media (with orientations of links)
* Share a project publicly
* Export / import projets

Quick start
-----------

To run the project locally :

```bash
cp docker-compose.override.sample.yml docker-compose.override.yml
docker-compose up -d
docker-compose exec web composer install
docker-compose exec web php bin/console doctrine:database:create
docker-compose exec web php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec web yarn install
docker-compose exec web yarn encore prod
sudo chown -R www-data:www-data ./var
```

Versions
--------

2022-12-20 : Early alpha version

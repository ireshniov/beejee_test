<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20190901090102
 * @package App\Migrations
 */
final class Version20190901090102 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create tasks table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_completed TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DROP TABLE tasks");
    }
}

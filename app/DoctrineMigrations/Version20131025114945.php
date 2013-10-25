<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131025114945 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE common_lines (common_id INT NOT NULL, commonline_id INT NOT NULL, INDEX IDX_C8B9B75C8DBC56F7 (common_id), INDEX IDX_C8B9B75CCF7E0154 (commonline_id), PRIMARY KEY(common_id, commonline_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE common_lines ADD CONSTRAINT FK_C8B9B75C8DBC56F7 FOREIGN KEY (common_id) REFERENCES common (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE common_lines ADD CONSTRAINT FK_C8B9B75CCF7E0154 FOREIGN KEY (commonline_id) REFERENCES common_line (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE delivery_note ADD related_quote_id INT DEFAULT NULL, ADD common_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E7D031FD5 FOREIGN KEY (related_quote_id) REFERENCES quote (id)");
        $this->addSql("ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E8DBC56F7 FOREIGN KEY (common_id) REFERENCES common (id)");
        $this->addSql("CREATE INDEX IDX_1E21328E7D031FD5 ON delivery_note (related_quote_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_1E21328E8DBC56F7 ON delivery_note (common_id)");
        $this->addSql("ALTER TABLE invoice ADD related_quote_id INT DEFAULT NULL, ADD related_delivery_note_id INT DEFAULT NULL, ADD common_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE invoice ADD CONSTRAINT FK_906517447D031FD5 FOREIGN KEY (related_quote_id) REFERENCES quote (id)");
        $this->addSql("ALTER TABLE invoice ADD CONSTRAINT FK_906517446ECC47B2 FOREIGN KEY (related_delivery_note_id) REFERENCES delivery_note (id)");
        $this->addSql("ALTER TABLE invoice ADD CONSTRAINT FK_906517448DBC56F7 FOREIGN KEY (common_id) REFERENCES common (id)");
        $this->addSql("CREATE INDEX IDX_906517447D031FD5 ON invoice (related_quote_id)");
        $this->addSql("CREATE INDEX IDX_906517446ECC47B2 ON invoice (related_delivery_note_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_906517448DBC56F7 ON invoice (common_id)");
        $this->addSql("ALTER TABLE common_line DROP FOREIGN KEY FK_618CAC678DBC56F7");
        $this->addSql("DROP INDEX IDX_618CAC678DBC56F7 ON common_line");
        // $this->addSql("ALTER TABLE common_line DROP common_id");
        $this->addSql("ALTER TABLE common DROP FOREIGN KEY FK_E5EC70512989F1FD");
        $this->addSql("ALTER TABLE common DROP FOREIGN KEY FK_E5EC70512CF3B78B");
        $this->addSql("ALTER TABLE common DROP FOREIGN KEY FK_E5EC7051DB805178");
        $this->addSql("DROP INDEX UNIQ_E5EC7051DB805178 ON common");
        $this->addSql("DROP INDEX UNIQ_E5EC70512CF3B78B ON common");
        $this->addSql("DROP INDEX UNIQ_E5EC70512989F1FD ON common");
        // $this->addSql("ALTER TABLE common DROP invoice_id, DROP delivery_note_id, DROP quote_id");
        $this->addSql("ALTER TABLE quote ADD common_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF48DBC56F7 FOREIGN KEY (common_id) REFERENCES common (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_6B71CBF48DBC56F7 ON quote (common_id)");

        $this->addSql("CREATE TABLE IF NOT EXISTS custom_session (session_id VARCHAR(255) NOT NULL, session_value LONGTEXT NOT NULL, session_time INT NOT NULL, PRIMARY KEY(session_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("DROP TABLE IF EXISTS `session`");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

/*        $this->addSql("CREATE TABLE session (session_id VARCHAR(255) NOT NULL, session_value LONGTEXT NOT NULL, session_time INT NOT NULL, PRIMARY KEY(session_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE commonline_common ADD CONSTRAINT FK_C9E9F86A8DBC56F7 FOREIGN KEY (common_id) REFERENCES common (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE commonline_common ADD CONSTRAINT FK_C9E9F86ACF7E0154 FOREIGN KEY (commonline_id) REFERENCES common_line (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE common ADD invoice_id INT DEFAULT NULL, ADD delivery_note_id INT DEFAULT NULL, ADD quote_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE common ADD CONSTRAINT FK_E5EC70512989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)");
        $this->addSql("ALTER TABLE common ADD CONSTRAINT FK_E5EC70512CF3B78B FOREIGN KEY (delivery_note_id) REFERENCES delivery_note (id)");
        $this->addSql("ALTER TABLE common ADD CONSTRAINT FK_E5EC7051DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_E5EC7051DB805178 ON common (quote_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_E5EC70512CF3B78B ON common (delivery_note_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_E5EC70512989F1FD ON common (invoice_id)");
        $this->addSql("ALTER TABLE common_line ADD common_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE common_line ADD CONSTRAINT FK_618CAC678DBC56F7 FOREIGN KEY (common_id) REFERENCES common (id)");
        $this->addSql("CREATE INDEX IDX_618CAC678DBC56F7 ON common_line (common_id)");
        $this->addSql("ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E7D031FD5");
        $this->addSql("ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E8DBC56F7");
        $this->addSql("DROP INDEX IDX_1E21328E7D031FD5 ON delivery_note");
        $this->addSql("DROP INDEX UNIQ_1E21328E8DBC56F7 ON delivery_note");
        $this->addSql("ALTER TABLE delivery_note DROP related_quote_id, DROP common_id");
        $this->addSql("ALTER TABLE invoice DROP FOREIGN KEY FK_906517447D031FD5");
        $this->addSql("ALTER TABLE invoice DROP FOREIGN KEY FK_906517446ECC47B2");
        $this->addSql("ALTER TABLE invoice DROP FOREIGN KEY FK_906517448DBC56F7");
        $this->addSql("DROP INDEX IDX_906517447D031FD5 ON invoice");
        $this->addSql("DROP INDEX IDX_906517446ECC47B2 ON invoice");
        $this->addSql("DROP INDEX UNIQ_906517448DBC56F7 ON invoice");
        $this->addSql("ALTER TABLE invoice DROP related_quote_id, DROP related_delivery_note_id, DROP common_id");
        $this->addSql("ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF48DBC56F7");
        $this->addSql("DROP INDEX UNIQ_6B71CBF48DBC56F7 ON quote");
        $this->addSql("ALTER TABLE quote DROP common_id");*/
    }


    public function postUp(Schema $schema) {
        $this->migratedBaseData();
        $this->migrateDataLines();
        $this->migrateDataInvoices();
    }

    protected function migratedBaseData() {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Move common id between tables
        $sql = 'UPDATE invoice AS i INNER JOIN common c ON i.id = c.invoice_id SET i.common_id = c.id;';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        // Move common id between tables
        $sql = 'UPDATE quote AS q INNER JOIN common c ON q.id = c.quote_id SET q.common_id = c.id';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        // Move common id between tables
        $sql = 'UPDATE delivery_note AS d INNER JOIN common c ON d.id = c.delivery_note_id SET d.common_id = c.id';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
    }

    protected function migrateDataInvoices () {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $sql = 'SELECT * FROM common c WHERE invoice_id IS NOT NULL';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $commons = $stmt->fetchAll();
        foreach ($commons as $common) {
            $invoice = $em->getRepository('TeclliureInvoiceBundle:Invoice')->find($common['invoice_id']);
            if ($common['quote_id']) {
                $quote = $em->getRepository('TeclliureInvoiceBundle:Quote')->find($common['quote_id']);
                $invoice->setRelatedQuote($quote);
            }
            if ($common['delivery_note_id']) {
                $deliveryNote = $em->getRepository('TeclliureInvoiceBundle:DeliveryNote')->find($common['delivery_note_id']);
                $invoice->setRelatedDeliveryNote($deliveryNote);
            }
            $em->persist($invoice);
        }
        $em->flush();
    }

    protected function migrateDataLines () {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $sql = 'SELECT * FROM common_line';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $lines = $stmt->fetchAll();
        $newLines = array();
        foreach ($lines as $key=>$line) {
            $sqlString = '('.$line['id'].','.$line['common_id'].')';
            $newLines[] = $sqlString;
        }

        $sql = 'INSERT INTO common_lines (commonline_id, common_id) VALUES '.implode(',', $newLines).'';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
    }
}

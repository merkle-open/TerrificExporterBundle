<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 03.01.13
 * Time: 11:25
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Terrific\ExporterBundle\Object\ValidationResult;
    use Terrific\ExporterBundle\Object\ValidationResultItem;
    use Terrific\ExporterBundle\Service\Log;

    abstract class AbstractValidateAction extends AbstractAction {


        /**
         *
         */
        protected function processValidationResults(ValidationResult $valRes, $filename) {
            // OUT
            $error = $valRes->hasErrors();

            $results = $valRes->toOutputString('[%1$s : %2$s] %3$s');

            /** @var $item ValidationResultItem */
            foreach ($results as $item) {
                $this->log(AbstractAction::LOG_LEVEL_WARN, "--- " . $item);
            }

            $resultCount = count($results);

            if ($resultCount == 0) {
                Log::info("Validated %s Found %d Issues.", array($filename, $resultCount));
            } else {
                Log::warn("Validated %s Found %d Issues.", array($filename, $resultCount));
            }
        }

    }
}

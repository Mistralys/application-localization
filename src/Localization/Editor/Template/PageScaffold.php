<?php
/**
 * File containing the {@link Localization_Editor} class.
 *
 * @package Localization
 * @subpackage Editor
 * @see Localization_Translator
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Editor\EditorException;
use AppUtils\ConvertHelper;
use AppUtils\JSHelper;
use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use AppUtils\PaginationHelper;
use AppUtils\FileHelper;
use function AppUtils\sb;

/**
 * User Interface handler for editing localization files.
 *
 * @package Localization
 * @subpackage Editor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Localization_Editor_Template_PageScaffold
{
/**
 * @var Localization_Editor
 */private $editor;

    public function __construct(Localization_Editor $editor)
    {
        $this->editor = $editor;
    }

    /**
     * @return string
     * @throws OutputBuffering_Exception
     */
    public function render() : string
    {
        OutputBuffering::start();

        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <meta name="description" content="">
            <title><?php echo $this->editor->getAppName() ?></title>
            <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
            <script src="https://kit.fontawesome.com/54212b9b2b.js" crossorigin="anonymous"></script>
            <script><?php echo $this->getJavascript() ?></script>
            <style><?php echo $this->getCSS() ?></style>
        </head>
        <body>
        <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
            <a class="navbar-brand" href="<?php echo $this->editor->getURL() ?>"><?php echo $this->editor->getAppName() ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarsExampleDefault">
                <?php $this->renderAppLocales(); ?>
                <?php
                $backURL = $this->editor->getBackURL();
                if(!empty($backURL))
                {
                    ?>
                    <a href="<?php echo $backURL ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>
                        <?php echo $this->editor->getBackButtonLabel(); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </nav>
        <main role="main" class="container">
            <div>
                <?php $this->renderPageContent(); ?>
            </div>
        </main>
        </body>
        </html>
        <?php

        return OutputBuffering::get();
    }

    protected function renderScannerWarningsList() : void
    {
        ?>
        <h1><?php pt('Warnings') ?></h1>
        <p class="abstract">
            <?php
            pts('The following shows all texts where the system decided that they cannot be translated.');
            ?>
        </p>
        <dl>
            <?php
            $warnings = $this->editor->getScannerWarnings();

            foreach($warnings as $warning)
            {
                ?>
                <dt><?php echo FileHelper::relativizePathByDepth($warning->getFile(), 3) ?>:<?php echo $warning->getLine() ?></dt>
                <dd><?php echo $warning->getMessage() ?></dd>
                <?php
            }

            ?>
        </dl>
        <?php
    }

    protected function displayList() : void
    {
        $strings = $this->editor->getFilteredStrings();

        if(empty($strings))
        {
            ?>
            <div class="alert alert-info">
                <?php pt('No matching strings found.') ?>
            </div>
            <?php

            return;
        }

        $total = count($strings);
        $page = $this->editor->getPageNumber();
        $pager = new PaginationHelper($total, $this->editor->getAmountPerPage(), $page);

        $keep = array_slice($strings, $pager->getOffsetStart(), $this->editor->getAmountPerPage());

        ?>
        <form method="post">
            <div class="form-hiddens">
                <?php
                $params = $this-> editor->getRequestParams();
                foreach($params as $name => $value) {
                    ?>
                    <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>">
                    <?php
                }
                ?>
            </div>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th><?php pt('Text') ?></th>
                    <th class="align-center"><?php pt('Translated?') ?></th>
                    <th class="align-center"><?php pt('Location') ?></th>
                    <th class="align-right"><?php pt('Sources') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($keep as $string)
                {
                    $this->renderTextEditorEntry($string);
                }
                ?>
                </tbody>
            </table>
            <?php
            if($pager->hasPages())
            {
                $prevUrl = $this->editor->getPaginationURL($pager->getPreviousPage());
                $nextUrl = $this->editor->getPaginationURL($pager->getNextPage());

                ?>
                <nav aria-label="<?php pt('Navigate available pages of texts.') ?>">
                    <ul class="pagination">
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $prevUrl ?>">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                        </li>
                        <?php
                        $numbers = $pager->getPageNumbers();
                        foreach($numbers as $number)
                        {
                            $url = $this->editor->getPaginationURL($number);

                            ?>
                            <li class="page-item <?php if($pager->isCurrentPage($number)) { echo 'active'; } ?>">
                                <a class="page-link" href="<?php echo $url ?>">
                                    <?php echo $number ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $nextUrl ?>">
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php
            }
            ?>
            <br>
            <p>
                <button type="submit" name="<?php echo $this->editor->getSaveVariableName() ?>" value="yes" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php pt('Save now') ?>
                </button>
            </p>
        </form>

        <?php
    }

    protected function renderTextEditorEntry(Localization_Scanner_StringHash $string) : void
    {
        $hash = $string->getHash();
        $text = $string->getText();

        if($text===null)
        {
            throw new EditorException(
                'String hash has no text',
                '',
                Localization_Editor::ERROR_STRING_HASH_WITHOUT_TEXT
            );
        }

        $previewText = $string->getTranslatedText();
        if(empty($previewText)) {
            $previewText = $text->getText();
        }

        $shortText =  $this->renderText($previewText, 50);

        $files = $string->getFiles();
        $labelID = JSHelper::nextElementID();

        ?>
        <tr class="string-entry inactive" onclick="Editor.Toggle('<?php echo $hash ?>')" data-hash="<?php echo $hash ?>">
            <td class="string-text"><?php echo $shortText ?></td>
            <td class="align-center string-status"><?php echo $this->renderStatus($string) ?></td>
            <td class="align-center"><?php echo $this->renderTypes($string) ?></td>
            <td class="align-right"><?php echo $this->renderFileNames($string) ?></td>
        </tr>
        <tr class="string-form">
            <td colspan="4">
                <label for="<?php echo $labelID ?>"><?php pt('Native text:') ?></label>
                <p class="native-text"><?php echo $this->renderText($text->getText()) ?></p>
                <p>
                    <textarea rows="4" id="<?php echo $labelID ?>" class="form-control" name="<?php echo $this->editor->getStringsVariableName() ?>[<?php echo $hash ?>]"><?php echo $string->getTranslatedText() ?></textarea>
                </p>
                <?php
                $explanation = $text->getExplanation();
                if(!empty($explanation))
                {
                    ?>
                    <p>
                        <?php pt('Context information:') ?><br>
                        <span class="native-text"><?php echo $explanation ?></span>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="Editor.Confirm('<?php echo $hash ?>')">
                        <?php ptex('OK', 'Button') ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="Editor.Toggle('<?php echo $hash ?>')">
                        <?php ptex('Cancel', 'Button') ?>
                    </button>
                </p>
                <div class="files-list">
                    <p>
                        <?php
                        $totalFiles = count($files);

                        if($totalFiles == 1)
                        {
                            pt('Found in a single file:');
                        }
                        else
                        {
                            pt('Found in %1$s files:', $totalFiles);
                        }
                        ?>
                    </p>
                    <div class="files-scroller">
                        <ul>
                            <?php
                            $locations = $string->getStrings();

                            foreach($locations as $location)
                            {
                                $file = $location->getSourceFile();
                                $line = $location->getLine();

                                $ext = FileHelper::getExtension($file);

                                if($ext == 'php') {
                                    $icon = 'fab fa-php';
                                } else if($ext == 'js') {
                                    $icon = 'fab fa-js-square';
                                } else {
                                    $icon = 'fas fa-file-code';
                                }

                                ?>
                                <li>
                                    <i class="<?php echo $icon ?>"></i>
                                    <?php echo $file ?><span class="line-number">:<?php echo $line ?></span>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }

    protected function renderText(string $text, int $cutAt=0) : string
    {
        if(empty($text)) {
            return '';
        }

        if($cutAt > 0) {
            $text = ConvertHelper::text_cut($text, $cutAt);
        }

        $text = htmlspecialchars($text);

        $vars = $this->editor->detectVariables($text);

        foreach($vars as $var) {
            $text = str_replace($var, '<span class="placeholder">'.$var.'</span>', $text);
        }

        return $text;
    }

    protected function getJavascript() : string
    {
        return FileHelper::readContents($this->editor->getInstallPath().'/js/editor.js');
    }

    protected function getCSS() : string
    {
        return FileHelper::readContents($this->editor->getInstallPath().'/css/editor.css');
    }

    private function renderAppLocales() : void
    {
        $locales = $this->editor->getAppLocales();

        if (empty($this->appLocales))
        {
            return;
        }

        $activeLocale = $this->editor->getActiveLocale();

        ?>
        <ul class="navbar-nav mr-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <?php pt('Text sources') ?>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <?php $this->renderSourceSelection(); ?>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <?php echo $activeLocale->getLabel() ?>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <?php
                    foreach ($locales as $locale)
                    {
                        ?>
                        <a class="dropdown-item" href="<?php echo $this->editor->getLocaleURL($locale) ?>">
                            <?php echo $locale->getLabel() ?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </li>
            <li class="nav-item">
                <a href="<?php echo $this->editor->getScanURL() ?>"
                   class="btn btn-light btn-sm"
                   title="<?php pt('Scan all source files to find translatable texts.') ?>"
                   data-toggle="tooltip">
                    <i class="fa fa-refresh"></i>
                    <?php pt('Scan') ?>
                </a>
            </li>
            <?php
                $this->renderNavScannerWarnings();
            ?>
        </ul>
        <?php
    }

    private function renderNavScannerWarnings() : void
    {
        $scanner = $this->editor->getScanner();

        if (!$scanner->hasWarnings())
        {
            return;
        }

        $title = sb()
            ->t('The last scan for translatable texts reported warnings.')
            ->t('Click for details.');

        ?>
        <li class="nav-item">
            <a href="<?php echo $this->editor->getWarningsURL() ?>">
                <span class="badge badge-warning"
                      title="<?php echo $title ?>"
                      data-toggle="tooltip">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?php echo $scanner->countWarnings() ?>
                </span>
            </a>
        </li>
        <?php
    }

    private function renderSourceSelection() : void
    {
        $sources = $this->editor->getSources();
        $activeSourceID = $this->editor->getActiveSource()->getID();
        $scanner = $this->editor->getScanner();

        foreach ($sources as $source)
        {
            ?>
            <a class="dropdown-item" href="<?php echo $this->editor->getSourceURL($source) ?>">
                <?php
                if ($source->getID() === $activeSourceID)
                {
                    ?>
                    <b><?php echo $source->getLabel() ?></b>
                    <?php
                }
                else
                {
                    echo $source->getLabel();
                }
                ?>
                <?php
                $untranslated = $source->getSourceScanner($scanner)->countUntranslated();
                if ($untranslated > 0)
                {
                    $title = tex(
                        '%1$s texts have not been translated in this text source.',
                        'Amount of texts',
                        $untranslated
                    );

                    ?>
                    (<span class="text-danger" title="<?php echo $title ?>">
                        <?php echo $untranslated ?>
                    </span>)
                    <?php
                }
                ?>
            </a>
            <?php
        }
    }

    private function renderPageContent() : void
    {
        if (!$this->editor->hasAppLocales())
        {
            $this->renderNoAppLocales();
            return;
        }

        if ($this->editor->isShowWarningsEnabled())
        {
            $this->renderScannerWarningsList();
            return;
        }

        $this->renderStringsList();
    }

    private function renderNoAppLocales() : void
    {
        ?>
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i>
            <b><?php pt('Nothing to translate:') ?></b>
            <?php pt('No application locales were added to translate to.') ?>
        </div>
        <?php
    }

    private function renderStringsList() : void
    {
        $activeSource = $this->editor->getActiveSource();
        $activeLocale = $this->editor->getActiveLocale();
        $scanner = $this->editor->getScanner();

        ?>
        <h1><?php echo $activeSource->getLabel() ?></h1>
        <?php $this->renderUIMessages(); ?>
        <p>
            <?php
            pt(
                'You are translating to %1$s',
                '<span class="badge badge-info">' . $activeLocale->getLabel() . '</span>'
            );
            ?><br>

            <?php pt('Found %1$s texts to translate.', $activeSource->getSourceScanner($scanner)->countUntranslated()) ?>
        </p>
        <br>
        <?php
        if (!$scanner->isScanAvailable())
        {
            ?>
            <div class="alert alert-primary" role="alert">
                <b><?php pt('No texts found:') ?></b>
                <?php pt('The source folders have not been scanned yet.') ?>
            </div>
            <p>
                <a href="<?php echo $this->editor->getScanURL() ?>" class="btn btn-primary">
                    <i class="fa fa-refresh"></i>
                    <?php pt('Scan files now') ?>
                </a>
            </p>
            <?php
        }
        else
        {
            echo $this->editor->getFilters()->renderForm();

            $this->displayList();
        }
    }

    protected function renderFileNames(Localization_Scanner_StringHash $hash) : string
    {
        $max = 2;
        $total = $hash->countFiles();
        $keep = $hash->getFileNames();
        $keepTotal = count($keep); // with duplicate file names, this can be less than the file total

        // add a counter of the additional files if the total
        // is higher than the maximum to show
        if($total > $max)
        {
            $length = $max;
            if($length > $keepTotal) {
                $length = $keepTotal;
            }

            $keep = array_slice($keep, 0, $length);
            $keep[] = '+'.($total - $length);
        }

        return implode(', ', $keep);
    }

    protected function renderStatus(Localization_Scanner_StringHash $hash) : string
    {
        if($hash->isTranslated()) {
            return '<i class="fa fa-check text-success"></i>';
        }

        return '<i class="fa fa-ban text-danger"></i>';
    }

    protected function renderTypes(Localization_Scanner_StringHash $hash) : string
    {
        $types = array();

        if($hash->hasLanguageType('PHP')) {
            $types[] = t('Server');
        }

        if($hash->hasLanguageType('Javascript')) {
            $types[] = t('Client');
        }

        return implode(', ', $types);
    }

    private function renderUIMessages() : void
    {
        if (empty($_SESSION['localization_messages']))
        {
            return;
        }

        foreach ($_SESSION['localization_messages'] as $def)
        {
            ?>
            <div class="alert alert-<?php echo $def['type'] ?>" role="alert">
                <?php echo $def['text'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="<?php pt('Close') ?>"
                        title="<?php pt('Dismiss this message.') ?>" data-toggle="tooltip">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
        }

        // reset the messages after having displayed them
        $_SESSION['localization_messages'] = array();
    }
}

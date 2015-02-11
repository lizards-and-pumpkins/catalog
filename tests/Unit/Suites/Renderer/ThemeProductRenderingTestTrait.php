<?php

namespace Brera\Renderer;

trait ThemeProductRenderingTestTrait
{
    private $uniquePathToken;

    /**
     * @return void
     */
    private function createTemporaryThemeFiles()
    {
        $themeDirectory = $this->getThemeDirectoryPath();
        $this->createDirectory($themeDirectory);

        $layoutDirectoryPath = $this->getLayoutDirectoryPath();
        $this->createDirectory($layoutDirectoryPath);

        $templateDirectoryPath = $this->getTemplateDirectoryPath();
        $this->createDirectory($templateDirectoryPath);

        $fileContent = <<<EOX
<snippet>
    <block name="product_details_snippet" class="Brera\Renderer\Block" template="{{path}}/1column.phtml">
        <block name="content" class="Brera\Product\Block\ProductDetailsPageBlock" template="{{path}}/view.phtml">
            <block name="image_gallery"
                   class="Brera\Product\Block\ProductImageGallery"
                   template="{{path}}/gallery.phtml" />
        </block>
    </block>
</snippet>
EOX;
        $fileContent = str_replace('{{path}}', $templateDirectoryPath, $fileContent);
        $filePath = $layoutDirectoryPath . DIRECTORY_SEPARATOR . 'product_details_snippet.xml';
        $this->createFile($filePath, $fileContent);

        $fileContent = '- Hi, I\'m a 1 column template!<br/>
<?= $this->getChildOutput(\'content\') ?>
';
        $filePath = $templateDirectoryPath . DIRECTORY_SEPARATOR . '1column.phtml';
        $this->createFile($filePath, $fileContent);

        $fileContent = '- And I\'m a gallery template.' . "\n";
        $filePath = $templateDirectoryPath . DIRECTORY_SEPARATOR . 'gallery.phtml';
        $this->createFile($filePath, $fileContent);

        $fileContent = 'Product details page content

<?= $this->getProductAttributeValue(\'name\') ?> (<?= $this->getProductId() ?>)

<?= $this->getChildOutput(\'image_gallery\') ?>
';
        $filePath = $templateDirectoryPath . DIRECTORY_SEPARATOR . 'view.phtml';
        $this->createFile($filePath, $fileContent);
    }

    /**
     * @return void
     */
    private function removeTemporaryThemeFiles()
    {
        $themeDirectoryPath = $this->getThemeDirectoryPath();
        $this->removeDirectoryAndItsContent($themeDirectoryPath);
    }

    /**
     * @return string
     */
    private function getLayoutDirectoryPath()
    {
        return $this->getThemeDirectoryPath() . DIRECTORY_SEPARATOR . 'layout';
    }

    /**
     * @return string
     */
    private function getTemplateDirectoryPath()
    {
        return $this->getThemeDirectoryPath() . DIRECTORY_SEPARATOR . 'template';
    }

    /**
     * @return string
     */
    private function getThemeDirectoryPath()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->getUniquePathToken();
    }

    /**
     * @param string $directoryPath
     * @return void
     */
    private function createDirectory($directoryPath)
    {
        if (!file_exists($directoryPath) || !is_dir($directoryPath)) {
            mkdir($directoryPath);
        }
    }

    /**
     * @param string $filePath
     * @param string $fileContent
     * @return void
     */
    private function createFile($filePath, $fileContent)
    {
        file_put_contents($filePath, $fileContent);
    }

    /**
     * @param $directoryPath
     * @return void
     */
    private function removeDirectoryAndItsContent($directoryPath)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directoryPath);
    }

    /**
     * @return string
     */
    private function getUniquePathToken()
    {
        if (empty($this->uniquePathToken)) {
            $this->uniquePathToken = uniqid();
        }

        return $this->uniquePathToken;
    }
}

<?php

namespace Brera\Renderer;

trait ThemeTestTrait
{
    /**
     * @return null
     */
    private function createTemporaryThemeFiles()
    {
        $layoutDirectoryPath = $this->getLayoutDirectoryPath();
        $this->createDirectory($layoutDirectoryPath);

        $templateDirectoryPath = $this->getTemplateDirectoryPath();
        $this->createDirectory($templateDirectoryPath);

        $fileContent = <<<EOX
<snippet>
    <block name="product_details_snippet" class="Brera\Renderer\Block" template="{{path}}/1column.phtml">
        <block name="content" class="Brera\Product\Block\ProductDetailsPage" template="{{path}}/view.phtml">
            <block name="image_gallery" class="Brera\Product\Block\ProductImageGallery" template="{{path}}/gallery.phtml" />
        </block>
    </block>
</snippet>
EOX;
        $fileContent = str_replace('{{path}}', $templateDirectoryPath, $fileContent);
        $filePath = $layoutDirectoryPath . DIRECTORY_SEPARATOR . 'product_details_snippet.xml';
        $this->createFile($filePath, $fileContent);

        $fileContent = '- Hi, I\'m a 1 column template!<br/>
<?php echo $this->getChildOutput(\'content\') ?>
';
        $filePath = $templateDirectoryPath . DIRECTORY_SEPARATOR . '1column.phtml';
        $this->createFile($filePath, $fileContent);

        $fileContent = '- And I\'m a gallery template.' . "\n";
        $filePath = $templateDirectoryPath . DIRECTORY_SEPARATOR . 'gallery.phtml';
        $this->createFile($filePath, $fileContent);

        $fileContent = 'Product details page content

<?php echo $this->getProductAttributeValue(\'name\') ?> (<?php echo $this->getProductId() ?>)

<?php echo $this->getChildOutput(\'image_gallery\') ?>
';
        $filePath = $templateDirectoryPath . DIRECTORY_SEPARATOR . 'view.phtml';
        $this->createFile($filePath, $fileContent);
    }

    /**
     * @return null
     */
    private function removeTemporaryThemeFiles()
    {
        $layoutDirectoryPath = $this->getLayoutDirectoryPath();
        $this->removeDirectoryAndItsContent($layoutDirectoryPath);

        $templateDirectoryPath = $this->getTemplateDirectoryPath();
        $this->removeDirectoryAndItsContent($templateDirectoryPath);
    }

    /**
     * @return string
     */
    private function getLayoutDirectoryPath()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'layout';
    }

    /**
     * @return string
     */
    private function getTemplateDirectoryPath()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'template';
    }

    /**
     * @param string $directoryPath
     * @return null
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
     * @return null
     */
    private function createFile($filePath, $fileContent)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        file_put_contents($filePath, $fileContent);
    }

    /**
     * @param $directoryPath
     * @return null
     */
    private function removeDirectoryAndItsContent($directoryPath)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directoryPath);
    }
}

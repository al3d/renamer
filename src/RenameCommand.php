<?php



namespace App;



use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;



class RenameCommand extends Command
{



    protected function configure()
    {
        $this
            ->setName('rename')
            ->addOption('src', null, InputOption::VALUE_REQUIRED)
            ->addOption('dest', null, InputOption::VALUE_REQUIRED)
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srcDir = realpath($input->getOption('src'));
        $destDir = $input->getOption('dest');

        if (!is_dir($srcDir)) {
            $output->writeln('<error>src isn\'t a directory</error>');
            return;
        }
        if (!file_exists($destDir)) {
            if (!mkdir($destDir, 0777, true)) {
                $output->writeln('<error>dest couldn\'t be created</error>');
                return;
            }
            // $user = `whoami`;
            // chown($destDir, trim($user));
        } elseif (!is_dir($destDir)) {
            $output->writeln('<error>dest isn\'t a directory</error>');
            return;
        }

        $finder = new Finder;
        $finder->files()->in($srcDir);

        $count = 0;
        $total = count($finder);
        $skipped = [];

        if (!$total) {
            $output->writeln('<info>No files to rename</info>');
            return;
        }

        $progress = new ProgressBar($output, $total);

        if ($total > 10000) {
            $progress->setRedrawFrequency(100);
        }

        $progress->start();

        foreach ($finder as $file) {
            $path = $file->getRealpath();
            if (!$date = $this->getExifDate($path)) {
                $date = \DateTime::createFromFormat('U', $file->getMTime());
            }

            $dir = $destDir . '/'
                 . $date->format('Y') . '/'
                 . $date->format('m') . '-'
                 . strtoupper($date->format('M'));

            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    $skipped[] = $file;
                    // $output->writeln('<error>Unable to create directory' . $dir . '</error><info>...skipping file</info>');
                    continue;
                }
            }

            $dirFinder = new Finder;
            $dirFinder->files()->in($dir);

            $filename = sprintf(
                '%02d.%s',
                (count($dirFinder) + 1),
                strtolower($file->getExtension())
            );

            $newFile = $dir . '/' . $filename;

            if (file_exists($newFile)) {
                    $skipped[] = $file;
                // $output->writeln('<error>File exists for ' . $file->getFilename() . '</error><info>...skipping file</info>');
                continue;
            }
            if (!rename($path, $newFile)) {
                    $skipped[] = $file;
                // $output->writeln('<error>Unable to rename ' . $file->getFilename() . '</error><info>...skipping file</info>');
                continue;
            }

            // if (substr(dirname($path), strlen($srcDir) + 1)) {
            //     $oldDir = dirname($path);
            //     $oldDirFinder = new Finder;
            //     $oldDirFinder->files()->in($oldDir);
            //     if (!count($oldDirFinder)) {
            //         @rmdir($oldDir);
            //     }
            // }

            $progress->advance();

            ++$count;
        }
        $progress->finish();
        $output->writeln("\n");
        if (count($skipped)) {
            $output->writeln('<error>' . count($skipped) . ' files skipped</error>');
            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                foreach ($skipped as $file) {
                    $output->writeln(' - ' . $file->getFilename());
                }
            }
        }
        $output->writeln('<info>' . $count . ' of ' . $total . ' files renamed</info>');
    }



    protected function getExifDate($path)
    {
        $exifdata = @exif_read_data($path);
        if (empty($exifdata['DateTimeOriginal'])) {
            return false;
        }
        $dateStr = preg_replace(
            '/((?:20|19)\d\d)\D*(\d\d)\D*(\d\d)\D*(\d\d)\D*(\d\d)\D*(\d\d)/',
            '$1-$2-$3 $4:$5:$6',
            $exifdata['DateTimeOriginal']
        );
        $date = new \DateTime($dateStr);
        return $date;
    }



}

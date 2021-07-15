<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="images")
     */
    private $asProjectImage;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="thumbnail")
     */
    private $asProjectThumbnail;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAsProjectImage(): ?Project
    {
        return $this->asProjectImage;
    }

    public function setAsProjectThumbnail(?Project $project): self
    {
        $this->asProjectThumbnail = $project;

        return $this;
    }

    public function getAsProjectThumbnail(): ?Project
    {
        return $this->asProjectThumbnail;
    }

    public function setAsProjectImage(?Project $project): self
    {
        $this->asProjectImage = $project;

        return $this;
    }

    public function toArray(): ?array
    {
        $image = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'as_project_thumbnail' => null,
            'as_project_image' => null,
        ];
        
        $image['as_project_thumbnail'] = (empty($this->asProjectThumbnail)) ?: $this->asProjectThumbnail->getId();
        $image['as_project_image'] = (empty($this->asProjectImage)) ?: $this->asProjectImage->getId();

        return $image;
    }
}

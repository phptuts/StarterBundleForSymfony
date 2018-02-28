<?php

namespace StarterKit\StartBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Constraints;

trait ImageTrait
{

    /**
     * @var string
     *
     * @ORM\Column(name="image_url", type="string", length=255, nullable=true)
     */
    protected $imageUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="image_id", type="string", nullable=true)
     */
    protected $imageId;

    /**
     * @var string This is s3, cloudinary, etc
     *
     * @ORM\Column(name="image_vendor", type="string", length=255, nullable=true)
     */
    protected $imageVendor;

    /**
     * @var UploadedFile
     *
     * @Constraints\NotBlank(groups={BaseUser::VALIDATION_IMAGE_REQUIRED})
     * @Constraints\Image(maxSize="7Mi", mimeTypes={"image/gif", "image/jpeg", "image/png"}, groups={BaseUser::VALIDATION_GROUP_DEFAULT})
     */
    protected $image;


    /**
     * @return UploadedFile
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param UploadedFile $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     *
     * @return $this
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getImageVendor()
    {
        return $this->imageVendor;
    }

    /**
     * @param string $imageVendor
     * @return $this
     */
    public function setImageVendor($imageVendor)
    {
        $this->imageVendor = $imageVendor;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * @param string $imageId
     * @return $this
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }


}
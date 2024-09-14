<?php

namespace App\Entity;

use App\Repository\MeasurementsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MeasurementsRepository::class)]
#[ORM\Table(name: 'measurements')]
class Measurement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $transmitterId = null;

    #[ORM\Column(nullable: true)]
    private ?float $vccAtmega = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addDatetime = null;

    #[ORM\Column(nullable: true)]
    private ?float $tempDhtHic = null;

    #[ORM\Column(nullable: true)]
    private ?float $tempDhtHif = null;

    #[ORM\Column(nullable: true)]
    private ?float $humidityDht = null;

    #[ORM\Column(nullable: true)]
    private ?float $pressureBmp = null;

    #[ORM\Column(nullable: true)]
    private ?float $altitudeBmp = null;

    #[ORM\Column(nullable: true)]
    private ?float $tempBmp = null;

    #[ORM\Column(nullable: true)]
    private ?int $visIs = null;

    #[ORM\Column(nullable: true)]
    private ?float $uvindexIs = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTransmitterId(): ?int
    {
        return $this->transmitterId;
    }

    public function setTransmitterId(int $transmitterId): static
    {
        $this->transmitterId = $transmitterId;

        return $this;
    }

    public function getVccAtmega(): ?float
    {
        return $this->vccAtmega;
    }

    public function setVccAtmega(?float $vccAtmega): static
    {
        $this->vccAtmega = $vccAtmega;

        return $this;
    }

    public function getAddDatetime(): string
    {
        return $this->addDatetime->format('Y-m-d H:i:s');
    }

    public function setAddDatetime(\DateTimeInterface $addDatetime): static
    {
        $this->addDatetime = $addDatetime;
        return $this;
    }

    public function getTempDhtHic(): ?float
    {
        return $this->tempDhtHic;
    }

    public function setTempDhtHic(?float $tempDhtHic): static
    {
        $this->tempDhtHic = $tempDhtHic;
        return $this;
    }

    public function getTempDhtHif(): ?float
    {
        return $this->tempDhtHif;
    }

    public function setTempDhtHif(?float $tempDhtHif): static
    {
        $this->tempDhtHif = $tempDhtHif;
        return $this;
    }

    public function getHumidityDht(): ?float
    {
        return $this->humidityDht;
    }

    public function setHumidityDht(?float $humidityDht): static
    {
        $this->humidityDht = $humidityDht;

        return $this;
    }

    public function getPressureBmp(): ?float
    {
        return $this->pressureBmp;
    }

    public function setPressureBmp(?float $pressureBmp): static
    {
        $this->pressureBmp = $pressureBmp;

        return $this;
    }

    public function getAltitudeBmp(): ?float
    {
        return $this->altitudeBmp;
    }

    public function setAltitudeBmp(?float $altitudeBmp): static
    {
        $this->altitudeBmp = $altitudeBmp;

        return $this;
    }

    public function getTempBmp(): ?float
    {
        return $this->tempBmp;
    }

    public function setTempBmp(?float $tempBmp): static
    {
        $this->tempBmp = $tempBmp;

        return $this;
    }

    public function getVisIs(): ?int
    {
        return $this->visIs;
    }

    public function setVisIs(?int $visIs): static
    {
        $this->visIs = $visIs;

        return $this;
    }

    public function getUvindexIs(): ?float
    {
        return $this->uvindexIs;
    }

    public function setUvindexIs(?float $uvindexIs): static
    {
        $this->uvindexIs = $uvindexIs;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

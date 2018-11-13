<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use Doctrine\ORM\Mapping as ORM;
use Core;
use Package;
use Controller;
use View;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStorePaymentMethods")
 */
class Method extends Controller
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $pmID;

    /** @ORM\Column(type="text") */
    protected $pmHandle;

    /** @ORM\Column(type="text") */
    protected $pmName;

    /** @ORM\Column(type="text", nullable=true) */
    protected $pmDisplayName;

    /** @ORM\Column(type="text", nullable=true) */
    protected $pmButtonLabel;

    /** @ORM\Column(type="boolean") */
    protected $pmEnabled;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $pmSortOrder;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pkgID;

    private $methodController;

    public function getID()
    {
        return $this->pmID;
    }

    public function getHandle()
    {
        return $this->pmHandle;
    }

    public function setHandle($handle)
    {
        $this->pmHandle = $handle;
    }

    public function getName()
    {
        return $this->pmName;
    }

    public function setName($name)
    {
        return $this->pmName = $name;
    }

    public function getButtonLabel()
    {
        return $this->pmButtonLabel;
    }

    public function setButtonLabel($pmButtonLabel)
    {
        $this->pmButtonLabel = $pmButtonLabel;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    public function setPackageID($pkgID)
    {
        $this->pkgID = $pkgID;
    }

    public function getSortOrder()
    {
        return $this->pmSortOrder;
    }

    public function setSortOrder($order)
    {
        $this->pmSortOrder = $order ? $order : 0;
    }

    public function getDisplayName()
    {
        if ("" == $this->pmDisplayName) {
            return $this->pmName;
        } else {
            return $this->pmDisplayName;
        }
    }

    public function setDisplayName($name)
    {
        $this->pmDisplayName = $name;
    }

    public function setEnabled($status)
    {
        $this->pmEnabled = (bool) $status;
    }

    public function isEnabled()
    {
        return $this->pmEnabled;
    }

    public static function getByID($pmID)
    {
        $em = \ORM::entityManager();
        $method = $em->find(get_class(), $pmID);

        if ($method) {
            $method->setMethodController();
        }

        return ($method instanceof self) ? $method : false;
    }

    public static function getByHandle($pmHandle)
    {
        $em = \ORM::entityManager();
        $method = $em->getRepository(get_class())->findOneBy(['pmHandle' => $pmHandle]);

        if ($method) {
            $method->setMethodController();
        }

        return ($method instanceof self) ? $method : false;
    }

    public function getMethodDirectory()
    {
        if ($this->pkgID > 0) {
            $pkg = Package::getByID($this->pkgID);
            $dir = $pkg->getPackagePath() . "/src/CommunityStore/Payment/Methods/" . $this->pmHandle . "/";
        }

        return $dir;
    }

    protected function setMethodController()
    {
        $th = Core::make("helper/text");
        $namespace = "Concrete\\Package\\" . $th->camelcase(Package::getByID($this->pkgID)->getPackageHandle()) . "\\Src\\CommunityStore\\Payment\\Methods\\" . $th->camelcase($this->pmHandle);

        $className = $th->camelcase($this->pmHandle) . "PaymentMethod";
        $namespace = $namespace . '\\' . $className;
        $this->methodController = new $namespace();
    }

    public function getMethodController()
    {
        return $this->methodController;
    }

    /*
     * @ORM\param string $pmHandle
     * @ORM\param string $pmName
     * @ORM\pkg Package Object
     * @ORM\param string $pmDisplayName
     * @ORM\param bool $enabled
     */
    public static function add($pmHandle, $pmName, $pkg = null, $pmButtonLabel = '', $enabled = false)
    {
        $pm = self::getByHandle($pmHandle);
        if (!($pm instanceof self)) {
            $paymentMethod = new self();
            $paymentMethod->setHandle($pmHandle);
            $paymentMethod->setName($pmName);
            $paymentMethod->setPackageID($pkg->getPackageID());
            $paymentMethod->setDisplayName($pmName);
            $paymentMethod->setButtonLabel($pmButtonLabel);
            $paymentMethod->setEnabled($enabled);
            $paymentMethod->save();
        }
    }

    public static function getMethods($enabled = false)
    {
        $em = \ORM::entityManager();
        if ($enabled) {
            $methods = $em->getRepository(get_class())->findBy(['pmEnabled' => 1], ['pmSortOrder' => 'ASC']);
        } else {
            $methods = $em->getRepository(get_class())->findBy([], ['pmSortOrder' => 'ASC']);
        }
        foreach ($methods as $method) {
            $method->setMethodController();
        }

        return $methods;
    }

    public static function getEnabledMethods()
    {
        return self::getMethods(true);
    }

    public function renderCheckoutForm()
    {
        $class = $this->getMethodController();
        $class->checkoutForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle . '/checkout_form', ['vars' => $class->getSets()], $pkg->getPackageHandle());
    }

    public function renderDashboardForm()
    {
        $controller = $this->getMethodController();
        $controller->dashboardForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle . '/dashboard_form', ['vars' => $controller->getSets()], $pkg->getPackageHandle());
    }

    public function renderRedirectForm()
    {
        $controller = $this->getMethodController();
        $controller->redirectForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle . '/redirect_form', ['vars' => $controller->getSets()], $pkg->getPackageHandle());
    }

    public function submitPayment()
    {
        //load controller
        $class = $this->getMethodController();

        return $class->submitPayment();
    }

    public function getPaymentMinimum()
    {
        return 0;
    }

    public function getPaymentMaximum()
    {
        return 1000000000; // raises pinky
    }

    public function save(array $data = [])
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $this->remove();
    }

    public function remove()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function isExternal()
    {
        return false;
    }

    public function markPaid()
    {
        return true;
    }

    public function sendReceipt()
    {
        return true;
    }

    // method stub
    public function redirectForm()
    {
    }

    // method stub
    public function checkoutForm()
    {
    }

    // method stub
    public function getPaymentInstructions()
    {
        return '';
    }
}

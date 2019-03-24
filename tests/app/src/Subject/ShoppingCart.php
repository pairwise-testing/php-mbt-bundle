<?php

namespace App\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Annotation\DataProvider;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class ShoppingCart extends AbstractSubject
{
    /**
     * @var array
     */
    protected $cart;

    /**
     * @var string Current viewing product
     */
    protected $product;

    /**
     * @var string Current viewing category
     */
    protected $category;

    /**
     * @var array
     */
    protected $featuredProducts = [
        '43', // 'MacBook',
        '40', // 'iPhone',
        '42', // 'Apple Cinema 30',
        '30', // 'Canon EOS 5D',
    ];

    /**
     * @var array
     */
    protected $categories = [
        '20', // 'Desktops',
        '20_27', // 'Mac',
        '18', // 'Laptops & Notebooks',
        '25', // 'Components',
        '25_28', // 'Monitors',
        '57', // 'Tablets',
        '17', // 'Software',
        '24', // 'Phones & PDAs',
        '33', // 'Cameras',
        '34', // 'MP3 Players',
    ];

    /**
     * @var array
     */
    protected $productsInCategory = [
        '20' => [
            '42', // 'Apple Cinema 30',
            '30', // 'Canon EOS 5D',
            '47', // 'HP LP3065',
            '28', // 'HTC Touch HD',
            '40', // 'iPhone',
            '48', // 'iPod Classic',
            '43', // 'MacBook',
            '44', // 'MacBook Air',
            '29', // 'Palm Treo Pro',
            '35', // 'Product 8',
            '33', // 'Samsung SyncMaster 941BW',
            '46', // 'Sony VAIO',
        ],
        '20_27' => [
            '41', // 'iMac',
        ],
        '18' => [
            '47', // 'HP LP3065',
            '43', // 'MacBook',
            '44', // 'MacBook Air',
            '45', // 'MacBook Pro',
            '46', // 'Sony VAIO',
        ],
        '25' => [],
        '25_28' => [
            '42', // 'Apple Cinema 30',
            '33', // 'Samsung SyncMaster 941BW'
        ],
        '57' => [
            '49', // 'Samsung Galaxy Tab 10.1',
        ],
        '17' => [],
        '24' => [
            '28', // 'HTC Touch HD',
            '40', // 'iPhone',
            '29', // 'Palm Treo Pro',
        ],
        '33' => [
            '30', // 'Canon EOS 5D',
            '31', // 'Nikon D300'
        ],
        '34' => [
            '48', // 'iPod Classic',
            '36', // 'iPod Nano',
            '34', // 'iPod Shuffle',
            '32', // 'iPod Touch',
        ],
    ];

    /**
     * @var array
     */
    protected $outOfStock = [
        '49', // 'Samsung Galaxy Tab 10.1',
    ];

    /**
     * @var array
     */
    protected $needOptions = [
        '42', // 'Apple Cinema 30',
        '30', // 'Canon EOS 5D',
        '35', // 'Product 8',
    ];

    public function __construct()
    {
        $this->cart = [];
        $this->category = null;
        $this->product = null;
    }

    public static function support(): string
    {
        return 'shopping_cart';
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomCategory")
     */
    public function viewAnyCategoryFromHome()
    {
        if (empty($this->data['category'])) {
            throw new Exception('Can not view category from home: category is not selected');
        }
        $category = $this->data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomCategory")
     */
    public function viewOtherCategory()
    {
        if (empty($this->data['category'])) {
            throw new Exception('Can not view category from other category: category is not selected');
        }
        $category = $this->data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomCategory")
     */
    public function viewAnyCategoryFromProduct()
    {
        if (empty($this->data['category'])) {
            throw new Exception('Can not view category from product: category is not selected');
        }
        $category = $this->data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomCategory")
     */
    public function viewAnyCategoryFromCart()
    {
        if (empty($this->data['category'])) {
            throw new Exception('Can not view category from cart: category is not selected');
        }
        $category = $this->data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromHome")
     */
    public function viewProductFromHome()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not view product from home: product is not selected');
        }
        $product = $this->data['product'];
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromCart")
     */
    public function viewProductFromCart()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not view product from cart: product is not selected');
        }
        $product = $this->data['product'];
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromCategory")
     */
    public function viewProductFromCategory()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not view product from category: product is not selected');
        }
        $product = $this->data['product'];
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @throws Exception
     */
    public function categoryHasSelectedProduct()
    {
        if (empty($this->productsInCategory[$this->category])) {
            return false;
        } else {
            if (empty($this->data['product'])) {
                throw new Exception('Can not check if category has selected product or not: product is not selected');
            }
            $product = $this->data['product'];

            return in_array($product, $this->productsInCategory[$this->category]);
        }
    }

    public function productHasBeenSelected()
    {
        return !empty($this->data['product']);
    }

    public function categoryHasBeenSelected()
    {
        return !empty($this->data['category']);
    }

    public function viewCartFromHome()
    {
        $this->category = null;
        $this->product = null;
    }

    public function viewCartFromCategory()
    {
        $this->category = null;
        $this->product = null;
    }

    public function viewCartFromProduct()
    {
        $this->category = null;
        $this->product = null;
    }

    public function viewCartFromCheckout()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromHome()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromCategory()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromProduct()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromCart()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromCategory()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromProduct()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromCart()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromCheckout()
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @throws Exception
     */
    public function cartHasSelectedProduct()
    {
        if (empty($this->cart)) {
            return false;
        } else {
            if (empty($this->data['product'])) {
                throw new Exception('Can not check if cart has selected product or not: product is not selected');
            }
            $product = $this->data['product'];

            return !empty($this->cart[$product]);
        }
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromHome")
     */
    public function addFromHome()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not add product from home: product is not selected');
        }
        $product = $this->data['product'];
        if (!$this->testing) {
            if (in_array($product, $this->needOptions)) {
                throw new Exception('You need to specify options for this product! Can not add product');
            }
        }
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        } else {
            ++$this->cart[$product];
        }
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromCategory")
     */
    public function addFromCategory()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not add product from category: product is not selected');
        }
        $product = $this->data['product'];
        if (!$this->testing) {
            if (in_array($product, $this->needOptions)) {
                throw new Exception('You need to specify options for this product! Can not add product');
            }
        }
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        } else {
            ++$this->cart[$product];
        }
    }

    /**
     * @throws Exception
     */
    public function addFromProduct()
    {
        if (!$this->testing) {
            if (in_array($this->product, $this->needOptions)) {
                throw new Exception('You need to specify options for this product! Can not add product');
            }
        }
        if (!isset($this->cart[$this->product])) {
            $this->cart[$this->product] = 1;
        } else {
            ++$this->cart[$this->product];
        }
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromCart")
     */
    public function remove()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not remove product from cart: product is not selected');
        }
        $product = $this->data['product'];
        unset($this->cart[$product]);
    }

    /**
     * @throws Exception
     * @DataProvider(method="getRandomProductFromCart")
     */
    public function update()
    {
        if (empty($this->data['product'])) {
            throw new Exception('Can not update product in cart: product is not selected');
        }
        $product = $this->data['product'];
        $this->cart[$product] = rand(1, 99);
    }

    public function home()
    {
    }

    public function category()
    {
    }

    public function product()
    {
    }

    public function cart()
    {
    }

    /**
     * @throws Exception
     */
    public function checkout()
    {
        if (!$this->testing) {
            foreach ($this->cart as $product => $quantity) {
                if (in_array($product, $this->outOfStock)) {
                    throw new Exception('You added an out-of-stock product into cart! Can not checkout');
                }
            }
        }
    }

    public function getRandomProductFromHome()
    {
        if (empty($this->featuredProducts)) {
            return null;
        }
        $product = $this->featuredProducts[array_rand($this->featuredProducts)];

        return ['product' => $product];
    }

    public function getRandomCategory()
    {
        if (empty($this->categories)) {
            return null;
        }
        $category = $this->categories[array_rand($this->categories)];

        return ['category' => $category];
    }

    public function getRandomProductFromCart()
    {
        if (empty($this->cart)) {
            return null;
        }
        $product = array_rand($this->cart);

        return ['product' => $product];
    }

    public function getRandomProductFromCategory()
    {
        if (!isset($this->productsInCategory[$this->category])) {
            return null;
        }
        $products = $this->productsInCategory[$this->category];
        if (empty($products)) {
            return null;
        }
        $product = $products[array_rand($products)];

        return ['product' => $product];
    }

    public function hasCoupon()
    {
        return true;
    }

    public function hasGiftCertificate()
    {
        return true;
    }

    public function getScreenshotUrl($bugId, $index)
    {
        return sprintf('http://localhost/mbt-api/bug-screenshot/%d/%d', $bugId, $index);
    }
}

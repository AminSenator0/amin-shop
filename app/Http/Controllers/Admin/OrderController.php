<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualOrderRequest;
use App\Http\Requests\Admin\UpdateOrderNotesRequest;
use App\Http\Requests\Admin\UpdateOrderPaymentStatusRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdateOrderTrackingRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\OrderService;
use App\Services\OrdersExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
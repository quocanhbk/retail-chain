
import 'package:bkrm/pages/humanResourceModule/employee/listEmployeePage.dart';
import 'package:bkrm/pages/humanResourceModule/shift/addNewShiftPage.dart';
import 'package:bkrm/pages/humanResourceModule/shift/listShiftPage.dart';
import 'package:bkrm/pages/inventoryModule//category/listCategoryPage.dart';
import 'package:bkrm/pages/inventoryModule/returnPurchasedSheet/listReturnPurchasedPage.dart';
import 'package:bkrm/pages/loginPage/secondarySplashScreen.dart';
import 'package:bkrm/pages/managementModule/customerManagement/listCustomerPage.dart';
import 'package:bkrm/pages/managementModule/historyPage.dart';
import 'package:bkrm/pages/managementModule/storeManage/detailbranchPage.dart';
import 'package:bkrm/pages/managementModule/supplierManagement/listSupplierPage.dart';
import 'package:bkrm/pages/sellerModule/invoice/listInvoicePage.dart';
import 'package:bkrm/pages/sellerModule/pos/posPage.dart';
import 'package:bkrm/pages/sellerModule/refund/listRefundPage.dart';
import 'package:bkrm/pages/sellerModule/shoppingCart/shoppingCart.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import 'package:bkrm/pages/humanResourceModule/employee/addNewEmployeeUser.dart';
import 'package:bkrm/pages/inventoryModule/InventoryPage.dart';
import 'package:bkrm/pages/inventoryModule/import/importGoodPage.dart';
import 'package:bkrm/pages/inventoryModule/purchasedSheet/purchasedSheetPage.dart';
import 'package:bkrm/pages/loginPage/loginPage.dart';
import 'package:bkrm/pages/loginPage/signup.dart';
import 'package:bkrm/pages/loginPage/welcomePage.dart';
import 'package:bkrm/pages/managementModule/dashboardPage.dart';
import 'package:bkrm/pages/loginPage/selectModulePage.dart';
import 'package:page_transition/page_transition.dart';

final navigatorKey = GlobalKey<NavigatorState>();
final pageTransitionType = PageTransitionType.rightToLeftWithFade;
class Nav2App extends StatelessWidget {

  static const  String welcomePage = '/';
  static const String loginRoute = '/login';
  static const String signUpRoute = '/signUp';
  static const String selectModuleroute = '/selectModule';
  static const String dashboardRoute = '/management/dashboard';
  static const String inventoryRoute = '/inventory';
  static const String importGoodRoute = '/inventory/importGood';
  static const String importInvoice = '/inventory/importInvoice';
  static const String listImportInvoice ='/inventory/listImportInvoice';
  static const String listReturnPurchaseSheet ='/inventory/listReturnPurchasedSheet';
  static const String posRoute = '/pos';
  static const String invoiceListRoute = '/pos/invoiceList';
  static const String refundListRoute = '/pos/refundList';
  static const String shoppingCartRoute = '/pos/shoppingCart';
  static const String addNewEmployeeRoute = '/hr/addNewEmployee';
  static const String viewAllEmployeeRoute = '/hr/viewAllEmployee';
  static const String addNewShiftRoute = '/hr/addNewShift';
  static const String listShiftRoute = '/hr/listShift';
  static const String detailBranchRoute = "/management/viewDetailBranch";
  static const String viewAllCustomerRoute = "/management/viewAllCustomerList";
  static const String viewAllSupplierRoute = "/management/viewAllSupplierList";
  static const String historyRoute = "/management/history";
  static const String viewAllCategoryRoute = "/inventory/viewAllCategoryList";


  @override
  Widget build(BuildContext context) {
    return MaterialApp(
        navigatorKey: navigatorKey,
        home: SecondarySplashScreen(),
        onGenerateRoute: (settings){
          switch (settings.name) {
            case '/':
              return MaterialPageRoute(
                settings: settings,
                  builder: (_) => WelcomePage());
            case '/selectModule':
              return PageTransition(settings: settings,child: SelectModulePage(), type: pageTransitionType);
            case '/login':
              return PageTransition(settings: settings, child: LoginPage(),type: pageTransitionType);
            case '/signUp':
              return PageTransition(settings: settings,child: SignUpPage(),type: pageTransitionType);
            case '/management/dashboard':
              return PageTransition(settings: settings,child:DashboardPage(),type: pageTransitionType);
            case '/inventory':
              return PageTransition(settings: settings,child: InventoryPage(title: "Kho Hàng",),type: pageTransitionType);
            case '/inventory/importGood':
              return PageTransition(settings: settings,child: ImportGoodPage(),type: pageTransitionType);
            case '/inventory/listImportInvoice':
              return PageTransition(settings: settings,child: ImportInvoicePage(),type: pageTransitionType);
            case '/inventory/listReturnPurchasedSheet':
              return PageTransition(settings: settings,child:ListReturnPurchasedPage(),type: pageTransitionType);
            case '/inventory/viewAllCategoryList':
              return PageTransition(settings: settings,child:ListCategoryPage(),type: pageTransitionType);
            case '/pos':
              return PageTransition(settings: settings,child:PosPage(title: "Bán Hàng",),type: pageTransitionType);
            case '/pos/invoiceList':
              return PageTransition(settings: settings,child:InvoicePage(),type: pageTransitionType);
            case '/pos/refundList':
              return PageTransition(settings: settings,child:ListRefundPage(),type: pageTransitionType);
            case '/pos/shoppingCart':
              return PageTransition(settings: settings,child: ShoppingCart(),type: pageTransitionType);
            case "/hr/addNewEmployee":
              return PageTransition(settings: settings,child:  AddNewEmployeeUserPage(),type: pageTransitionType);
            case "/hr/viewAllEmployee":
              return PageTransition(settings: settings,child:ListEmployeePage(),type: pageTransitionType);
            case "/hr/addNewShift":
              return PageTransition(settings: settings,child:AddNewShiftPage(),type: pageTransitionType);
            case "/hr/listShift":
              return PageTransition(settings: settings,child:ListShiftPage(),type: pageTransitionType);
            case "/management/viewAllCustomerList":
              return PageTransition(settings: settings,child: ListCustomerPage(),type: pageTransitionType);
            case "/management/viewAllSupplierList":
              return PageTransition(settings: settings,child: ListSupplierPage(),type: pageTransitionType);
            case '/management/viewDetailBranch':
              return PageTransition(settings: settings,child: DetailBranch(),type: pageTransitionType);
            case '/management/history':
              return PageTransition(settings: settings,child: HistoryPage(),type: pageTransitionType);
            default:
              return MaterialPageRoute(
                  builder: (_) => Scaffold(
                    body: Center(
                        child: Text('No route defined for ${settings.name}')),
                  ));
          }
        },
      );
  }
}

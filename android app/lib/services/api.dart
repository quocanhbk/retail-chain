import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/loginPage/loginPage.dart';
import 'package:bkrm/services/services.dart';
import 'package:dio/adapter.dart';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import 'info/managementInfo/userInfo.dart';

enum ApiType { login, getData, sendData }

class ServerConfig {
  static final String ipMergedDb = "https://149.28.148.73/merged-db/";
  static final String ipLocal = "https://149.28.148.73/bkrm/";
  static final String projectUrl = ipLocal + "public/";
  static final String apiUrl = projectUrl + "api/";
}

class MyHttpOverrides extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (X509Certificate cert, String host, int port) {
        if (host == "149.28.148.73") {
          return true;
        }
        return false;
      };
  }
}

class ApiService {
  static final ApiService _instance = ApiService._internal();
  bool isRefresh = false;
  factory ApiService() {
    return _instance;
  }

  Dio dio = Dio();

  ApiService._internal() {
    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (request, handler) {
        if(request.queryParameters["token"]==null&&!request.path.contains("token")){
          if (BkrmService().currentUser != null) {
            request.queryParameters["token"] = BkrmService().currentUser!.token!;
          }
        }
        debugPrint(request.path+" queryParameter : "+request.queryParameters.toString());
        debugPrint("data: "+request.data.toString());
        handler.next(request);
      },
      onResponse: (response, handler) async {
        if (response.data["state"] == "jwt_error" &&
            response.requestOptions.path != ServerConfig.apiUrl + "refresh" &&
            !isRefresh) {
          debugPrint("Refresh token");
          final refreshReponse = await refreshToken();
          if (refreshReponse["state"] == "success") {
            BkrmService().currentUser!.token =
                refreshReponse["token"].toString();
            final prevResponse = await dio.fetch(response.requestOptions);
            return handler.resolve(prevResponse);
          } else {
            if (refreshReponse["state"] == "jwt_error") {
              showDialog(
                  barrierDismissible: false,
                  context: navigatorKey.currentContext!,
                  builder: (context) {
                    return AlertDialog(
                      title: Text("Cảnh báo"),
                      content: Container(
                        child:
                            Column(mainAxisSize: MainAxisSize.min, children: [
                          Text(
                            "Phát hiện tài khoản đang được đăng nhập trên một thiết bị khác hoặc bị vô hiệu hóa. Đang đăng xuất ...",
                            textAlign: TextAlign.center,
                            style: TextStyle(
                                fontWeight: FontWeight.w500, fontSize: 14),
                          ),
                          SizedBox(
                            height: 15,
                          ),
                          Container(
                            height: 50,
                            child: Center(
                              child: CircularProgressIndicator(),
                            ),
                          )
                        ]),
                      ),
                    );
                  });
              await BkrmService().logOut();
              await BkrmService().networkAvailableCheck();
              UserInfo? lastLogedIn = await BkrmService().getLastLogInUser();
              await Future.delayed(Duration(seconds: 1));
              Navigator.pushAndRemoveUntil(navigatorKey.currentContext!,MaterialPageRoute(builder: (context){
                return LoginPage(lastLoggedIn: lastLogedIn,);
              }),(route)=>false);
            } else {
              showDialog(
                  barrierDismissible: false,
                  context: navigatorKey.currentContext!,
                  builder: (context) {
                    return AlertDialog(
                      title: Text("Thông báo"),
                      content: Container(
                        child: Column(children: [
                          Text(
                            "Không thể xác minh tài khoản đang đăng nhập. Đang đăng xuất ...",
                            textAlign: TextAlign.center,
                            style: TextStyle(
                                fontWeight: FontWeight.w500, fontSize: 14),
                          ),
                          Container(
                            height: 50,
                            child: Center(
                              child: CircularProgressIndicator(),
                            ),
                          )
                        ]),
                      ),
                    );
                  });
              await BkrmService().logOut();
              await BkrmService().networkAvailableCheck();
              UserInfo? lastLogedIn = await BkrmService().getLastLogInUser();
                await Future.delayed(Duration(seconds: 1));
                Navigator.pushAndRemoveUntil(navigatorKey.currentContext!,MaterialPageRoute(builder: (context){
                  return LoginPage(lastLoggedIn: lastLogedIn,);
                }),(route)=>false);
            }
            return handler.reject(DioError(
                requestOptions: response.requestOptions,
                type: DioErrorType.cancel));
          }
        } else {
          return handler.resolve(response);
        }
      },
    ));
    (dio.httpClientAdapter as DefaultHttpClientAdapter).onHttpClientCreate =
        (HttpClient client) {
      client.badCertificateCallback =
          (X509Certificate cert, String host, int port) {
        if (host == "149.28.148.73") {
          return true;
        }
        return false;
      };
      return client;
    };
    HttpOverrides.global = new MyHttpOverrides();
  }

  Future<Map<String, dynamic>> refreshToken({String? token}) async {
    String url = ServerConfig.apiUrl + "refresh";
    try {
      Map<String,dynamic>? queryParameter = {};
      if(token!=null){
        queryParameter["token"]=token;
      }
      var response = await dio.post(url,queryParameters: queryParameter).timeout(new Duration(seconds: 5));
      Map<String, dynamic> returnCode = response.data;
      returnCode["statusCode"] = response.statusCode;
      if (response.statusCode != 200) {
        return returnCode;
      }
      returnCode['statusCode'] = response.statusCode;
      return returnCode;
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    } on DioError catch(e){
      if(e.message=="Http status error [500]"){
        return {"statusCode": 500};
      }else{
        debugPrint(e.message.toString());
        return {};
      }
    }

  }

  Future<Map<String, dynamic>> pushQueuedRequest(
      Map<String, dynamic> queuedRequest) async {
    var returnStatus = await dio.post(queuedRequest["path"],
        data: queuedRequest["data"],
        options: Options(listFormat: ListFormat.multiCompatible));
    return returnStatus.data;
  }

  ///Login - Sign up - Log out - Change password/////////////////////////
  Future<Map<String, dynamic>> login(String username, String password) async {
    print("Begin login");
    String url = ServerConfig.apiUrl + "login";
    try {
      var response = await dio.post(url, data: {
        "username": username,
        "password": password
      }).timeout(new Duration(seconds: 5));
      Map<String, dynamic> returnCode = response.data;
      returnCode["statusCode"] = response.statusCode;
      if (response.statusCode != 200) {
        return returnCode;
      }
      returnCode['statusCode'] = response.statusCode;
      return returnCode;
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> signUp(
      {required String name,
      required String email,
      required String phone,
      required String password,
      required String username,
      required String gender,
      required String dateOfBirth,
      required String branchName,
      required String branchAddress}) async {
    print("Begin sign up");
    String url = ServerConfig.apiUrl + "register";
    try {
      var response = await dio.post(url, data: {
        "name": name,
        "email": email,
        "username": username,
        "password": password,
        "phone": phone,
        "gender": gender.toLowerCase(),
        "date_of_birth": dateOfBirth,
        "store_name": branchName,
        "branch_name": branchName,
        "branch_address": branchAddress
      });
      Map<String, dynamic> returnCode = response.data;
      returnCode["statusCode"] = response.statusCode;
      return returnCode;
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> logOut() async {
    String url = ServerConfig.apiUrl + "logout";
    try {
      debugPrint(url);
      var response = await dio.post(url);
      debugPrint(response.data.toString());
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> changePassword(
      int storeId, int branchId, Map<String, dynamic> passwordMap) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/user/change-pass";
    try {
      var response = await dio.post(url, data: passwordMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }
  ///////////////////////////////////////////
  ///////////////////////////////////////////////////////

  ///////////////////////////////////////////////////////////
  ///////Create method ///////////////////////////////////

  Future<Map<String, dynamic>> createEmployeeUser(
      Map<String, dynamic> employeeMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/employee/create";
    try {
      var response = await dio.post(url, data: employeeMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = {};
        returnStatus["state"] = response.data["state"];
        returnStatus["errors"] = response.data["errors"];
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createCustomer(
      Map<String, dynamic> customerMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/customer/create";
    try {
      if (!BkrmService().networkAvailable) {
        BkrmService().addRequestToQueue(url, customerMap);
        return {"statusCode": 502, "state": "success"};
      }
      var response = await dio.post(url, data: customerMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = {};
        returnStatus["state"] = response.data["state"];
        returnStatus["errors"] = response.data["errors"];
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createSupplier(
      Map<String, dynamic> customerMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/supplier/create";
    try {
      var response = await dio.post(url, data: customerMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = {};
        returnStatus["state"] = response.data["state"];
        returnStatus["errors"] = response.data["errors"];
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createInvoice(
      Map<String, dynamic> invoiceMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/invoice/create";
    debugPrint("Return invoice");
    debugPrint(invoiceMap.toString());
    if (!BkrmService().networkAvailable) {
      BkrmService().addRequestToQueue(url, invoiceMap);
      return {"state": "success", "statusCode": 200};
    }
    try {
      var response = await dio.post(url, data: invoiceMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> responseMap = response.data;
        responseMap["statusCode"] = 200;
        return responseMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createNewProduct(
      Map<String, dynamic> productMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/create";
    try {
      debugPrint(url);
      FormData formData = FormData.fromMap(productMap);
      var response = await dio.post(url, data: formData);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createCategory(
      Map<String, dynamic> categoryMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/category/create";
    try {
      var response = await dio.post(url, data: categoryMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createImportInvoice(
      Map<String, dynamic> importInvoiceMap,
      int? storeId,
      int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/purchased-sheet/create";
    try {
      var response = await dio.post(url, data: importInvoiceMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createRefundSheet(
      Map<String, dynamic> refundSheetMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/refund-sheet/create";
    try {
      var response = await dio.post(url, data: refundSheetMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createRefundPurchaseSheet(
      Map<String, dynamic> refundSheetMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/return-purchased-sheet/create";
    try {
      debugPrint(refundSheetMap.toString());
      var response = await dio.post(url, data: refundSheetMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createShift(
      Map<String, dynamic> shiftMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/shift/create";
    try {
      var response = await dio.post(url, data: shiftMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createSchedule(
      Map<String, dynamic> scheduleMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/schedule/create";
    try {
      var response = await dio.post(url, data: scheduleMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> createAttendance(
      Map<String, dynamic> attendanceMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/attendance/create";
    try {
      var response = await dio.post(url, data: attendanceMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  ///Tạo phiếu kiểm kê hàng
  Future<Map<String, dynamic>> createQuantityCheckingSheet(
      Map<String, dynamic> inventoryMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/quantity-checking-sheet/create";
    try {
      var response = await dio.post(url, data: inventoryMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }
  ////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////
  ///////Get Method//////////////////////////////////////
  Future<Map<String, dynamic>> getUserInfo(
      int storeId, int branchId,String token) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/user/get";
    try {
      var response = await dio.get(url,queryParameters: {"token":token});
      if (response.statusCode == 200) {
        Map<String, dynamic> itemMap = response.data;
        itemMap["statusCode"] = response.statusCode;
        return itemMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getItemInfoNoPagination(
      int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/get";
    try {
      var response = await dio.get(url,
          queryParameters: {"is_get_all": 1},
          options: Options(listFormat: ListFormat.multiCompatible));
      if (response.statusCode == 200) {
        Map<String, dynamic> itemMap = response.data;
        itemMap["statusCode"] = response.statusCode;
        return itemMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getItemInfoPagination(
      int? storeId, int? branchId,
      {required int page, required Map<String, dynamic> filterMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/get";
    try {
      filterMap["page"] = page;
      debugPrint(filterMap.toString());
      var response = await dio.get(url,
          queryParameters: filterMap,
          options: Options(listFormat: ListFormat.multiCompatible));
      if (response.statusCode == 200) {
        Map<String, dynamic> itemMap = response.data;
        itemMap["statusCode"] = response.statusCode;
        return itemMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> searchItem(
      Map<String, dynamic> searchMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/search";
    try {
      debugPrint(url);
      debugPrint(searchMap.toString());
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> itemMap = response.data;
        itemMap["statusCode"] = response.statusCode;
        return itemMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> searchDefaultItem(
      Map<String, dynamic> searchMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/defaultitem/search";
    try {
      debugPrint(url);
      debugPrint(searchMap.toString());
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> itemMap = response.data;
        itemMap["statusCode"] = response.statusCode;
        return itemMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getDashboardInfo(
      int? storeId, int? branchId) async {
    print("Begin get infor of pages.dashboard");
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/dashboard/get";
    try {
      var response = await dio.get(url);
      if (response.statusCode == 200) {
        Map<String, dynamic> jsonMap = response.data;
        jsonMap["statusCode"] = response.statusCode;
        return jsonMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getCategory(int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/category/get";
    try {
      var response = await dio.get(url);
      if (response.statusCode == 200) {
        Map<String, dynamic> categoryMap = response.data;
        categoryMap["statusCode"] = response.statusCode;
        return categoryMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getAllEmployees(
      int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/employee/get";
    try {
      var response = await dio.get(url);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getAllCustomers(int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/customer/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getSuppliers(int? storeId, int? branchId,
      {String? name}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/supplier/get";
    try {
      Response response;
      if (name != null) {
        response = await dio.get(url, queryParameters: {"searched_text": name});
      } else {
        response = await dio.get(url);
      }
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getPurchasedSheet(int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/purchased-sheet/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getOnePurchasedSheet(
      int? storeId, int? branchId, Map<String, dynamic> searchMap) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/purchased-sheet/detail";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getInvoice(int? storeId, int? branchId,
      {Map<String, dynamic>? filterMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/invoice/get";
    try {
      debugPrint(url);
      debugPrint(filterMap.toString());
      var response = await dio.get(url, queryParameters: filterMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getOneInvoice(
      int? storeId, int? branchId, Map<String, dynamic> searchMap) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/invoice/detail";
    try {
      debugPrint("searchMap:" + searchMap.toString());
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getRefundSheet(int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/refund-sheet/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getOneRefundSheet(
      int? storeId, int? branchId, Map<String, dynamic> searchMap) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/refund-sheet/detail";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getReturnPurchasedSheet(
      int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/return-purchased-sheet/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getOneReturnPurchasedSheet(
      int? storeId, int? branchId, Map<String, dynamic> searchMap) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/return-purchased-sheet/detail";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getShifts(int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/shift/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getSchedules(int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/schedule/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getAttendances(int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/attendance/get";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>?> getAttendancesFromDateToDate(
      int? storeId, int? branchId,
      {Map<String, dynamic>? searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/attendance/detail";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getReportRevenue(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/report/revenue";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getReportItems(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/report/item";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getReportCategories(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/report/category";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getReportCustomers(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/report/customer";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getReportSupplier(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/report/supplier";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getPriceHistory(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/price-history";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getQuantityHistory(int? storeId, int? branchId,
      {required Map<String, dynamic> searchMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/quantity-history";
    try {
      var response = await dio.get(url, queryParameters: searchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getItemsWithNoPurchasePrice(
      int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/check-purchase-price";
    try {
      var response = await dio.get(url);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> getHistory(
      int? storeId, int? branchId,{Map<String,dynamic>? filterMap}) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/history";
    try {
      var response = await dio.get(url,queryParameters: filterMap);
      if (response.statusCode == 200) {
        Map<String, dynamic>? returnMap;
        returnMap = response.data;
        returnMap!["statusCode"] = response.statusCode;
        return returnMap;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }
  ///////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////

  ////////////////////////////////////////////////////////////////////
  ///////Edit method ///////////////////////////////////////////////////
  Future<Map<String, dynamic>> editItems(
      Map<String, dynamic> productMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/edit";
    try {
      FormData formData = FormData.fromMap(productMap);
      var response = await dio.post(url, data: formData);
      if (response.statusCode == 200) {
        response.data["statusCode"] = response.statusCode;
        return response.data;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editCurrentUser(
      Map<String, dynamic> userMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/user/edit";
    try {
      FormData formMap = FormData.fromMap(userMap);
      var response = await dio.post(url, data: formMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editCustomer(
      Map<String, dynamic> customerMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/customer/edit";
    try {
      var response = await dio.post(url, data: jsonEncode(customerMap));
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editSupplier(
      Map<String, dynamic> customerMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/supplier/edit";
    try {
      var response = await dio.post(url, data: customerMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editCategory(
      Map<String, dynamic> categoryMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/category/edit";
    try {
      var response = await dio.post(url, data: categoryMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editShift(
      Map<String, dynamic> shiftMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/shift/edit";
    try {
      debugPrint(shiftMap.toString());
      var response = await dio.post(url, data: shiftMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editBranch(
      Map<String, dynamic> branchMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/branch/edit";
    try {
      debugPrint(branchMap.toString());
      var response = await dio.post(url, data: branchMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> resetPasswordForOwner(
      Map<String, dynamic> resetPassMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/employee/reset-password";
    try {
      var response = await dio.post(url, data: resetPassMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> editEmployeeForOwner(
      Map<String, dynamic> employeeRolesMap,
      int? storeId,
      int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/employee/edit-info";
    try {
      var response = await dio.post(url, data: employeeRolesMap);
      if (response.statusCode == 200) {
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }
  ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////
//////////Delete method///////////////////////////////////////////
  Future<Map<String, dynamic>> deleteItem(
      Map<String, dynamic> itemMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/item/delete";
    try {
      var response = await dio.post(url, data: itemMap);
      if (response.statusCode == 200) {
        debugPrint(response.data.toString());
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> deleteSchedule(
      Map<String, dynamic> scheduleMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/schedule/delete";
    try {
      var response = await dio.post(url, data: scheduleMap);
      if (response.statusCode == 200) {
        debugPrint(response.data.toString());
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }

  Future<Map<String, dynamic>> changeStatusAccount(
      Map<String, dynamic> accountMap, int? storeId, int? branchId) async {
    String url = ServerConfig.apiUrl +
        "store/" +
        storeId.toString() +
        "/branch/" +
        branchId.toString() +
        "/employee/change-status";
    try {
      var response = await dio.post(url, data: accountMap);
      if (response.statusCode == 200) {
        debugPrint(response.data.toString());
        Map<String, dynamic> returnStatus = response.data;
        returnStatus["statusCode"] = response.statusCode;
        return returnStatus;
      } else {
        return {"statusCode": response.statusCode};
      }
    } on TimeoutException catch (e) {
      return {"statusCode": 502};
    }
  }
}



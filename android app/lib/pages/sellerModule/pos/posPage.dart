import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:bkrm/widget/sortListCriteria.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:bkrm/widget/listProducts.dart';
import 'package:bkrm/widget/listCategory.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/cartService.dart';

class PosPage extends StatefulWidget {
  PosPage({Key? key, this.title}) : super(key: key);
  final String? title;

  @override
  _PosPageState createState() => new _PosPageState();
}

class _PosPageState extends State<PosPage> {
  SnackBar? currentSnackBar;
  GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey();
  late ListProduct listProduct;
  late SortListCriteriaProduct sortListCriteria;
  late ListCategory listCategory;
  @override
  void initState() {
    super.initState();
    listProduct = ListProduct(
      hasSlider: true,
      onTapOnProduct: (context, ItemInfo rawDataItem) {
        ScaffoldMessenger.of(_scaffoldKey.currentContext!).hideCurrentSnackBar();
        BkrmService bkrmService = BkrmService();
        bkrmService.cart!.addCartItem(rawDataItem, 1);
        currentSnackBar=SnackBar(
          content: Text("Đã thêm thành công 1 sản phẩm vào giỏ hàng"),
          action: SnackBarAction(
            label: "Huỷ",
            onPressed: () {
              if(!bkrmService.cart!.empty){
                CartItem cartItem =
                bkrmService.cart!.getCartItem(rawDataItem.itemId);
                if (cartItem.amount > 1) {
                  cartItem.amount -= 1;
                  bkrmService.cart!.modifyCartItem(cartItem);
                } else {
                  bkrmService.cart!.removeCartItem(cartItem);
                }
                ScaffoldMessenger.of(_scaffoldKey.currentContext!)
                    .showSnackBar(SnackBar(
                  content: Text("Đã huỷ thành công"),
                  action: SnackBarAction(
                      label: "Ẩn",
                      onPressed: () {
                        ScaffoldMessenger.of(_scaffoldKey.currentContext!)
                            .hideCurrentSnackBar();
                      }),
                ));
              }
            },
          ),
        );
        ScaffoldMessenger.of(_scaffoldKey.currentContext!).showSnackBar(currentSnackBar!);
      },
      onLongPressOnProduct: (context,ItemInfo rawDataItem){
        debugPrint("On long press");
        showDialog(context: context, builder: (context){
          bool numberValid = false;
          TextEditingController controller = TextEditingController();
          return AlertDialog(
            title: Text("Nhập số lượng"),
            content:
              TextFormField(
                inputFormatters: [CustomerFormatter().numberFormatter],
                controller: controller,
                autovalidateMode: AutovalidateMode.always,
                keyboardType: TextInputType.number,
                validator: (value){
                  if(value==""||value==null){
                    numberValid=false;
                    return " *Bắt buộc";
                  }
                  if((int.tryParse(value)??0)<0){
                    numberValid=false;
                    return " *Lớn hơn 0";
                  }
                  numberValid=true;
                  return null;
                },

              ),
            actions: [
              TextButton(onPressed: (){Navigator.pop(context);}, child: Text("Hủy")),
              TextButton(onPressed: ()async{
                if(numberValid){
                  BkrmService().cart!.addCartItem(rawDataItem, int.tryParse(controller.value.text)??0);
                  Navigator.pop(context);
                  ScaffoldMessenger.of(_scaffoldKey.currentContext!).hideCurrentSnackBar();
                  currentSnackBar=SnackBar(
                    content: Text("Đã thêm thành công 1 sản phẩm vào giỏ hàng"),
                    action: SnackBarAction(
                      label: "Huỷ",
                      onPressed: () {
                        if(!BkrmService().cart!.empty){
                          CartItem cartItem =
                          BkrmService().cart!.getCartItem(rawDataItem.itemId);
                          if (cartItem.amount > 1) {
                            cartItem.amount -= 1;
                            BkrmService().cart!.modifyCartItem(cartItem);
                          } else {
                            BkrmService().cart!.removeCartItem(cartItem);
                          }
                          ScaffoldMessenger.of(_scaffoldKey.currentContext!)
                              .showSnackBar(SnackBar(
                            content: Text("Đã huỷ thành công"),
                            action: SnackBarAction(
                                label: "Ẩn",
                                onPressed: () {
                                  ScaffoldMessenger.of(_scaffoldKey.currentContext!)
                                      .hideCurrentSnackBar();
                                }),
                          ));
                        }
                      },
                    ),
                  );
                  ScaffoldMessenger.of(_scaffoldKey.currentContext!).showSnackBar(currentSnackBar!);
                }
              },child: Text("Thêm vào giỏ"),)
            ],
          );
        });
      },
    );
    sortListCriteria = SortListCriteriaProduct(listProduct);
    listCategory = ListCategory(listProduct);
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: ()async{
        ScaffoldMessenger.of(context).hideCurrentSnackBar();
        Navigator.pop(context);
        return false;
      },
      child: GestureDetector(
        onTap: () {
          FocusScope.of(context).requestFocus(FocusNode());
        },
        child: new Scaffold(
          resizeToAvoidBottomInset: false,
          key: _scaffoldKey,
          appBar: new AppBar(
            title: new Text(widget.title!),
            actions: [
/*            IconButton(
                  icon: Icon(Icons.shopping_cart),
                  onPressed: () {
                    ScaffoldMessenger.of(_scaffoldKey.currentContext!).hideCurrentSnackBar();
                    Navigator.push(context, MaterialPageRoute(builder: (context) {
                      return ShoppingCart();
                    }));
                  }),*/
              IconButton(
                  icon: Icon(Icons.qr_code),
                  onPressed: () async {
                    var status = await Permission.camera.status;
                    if (status.isPermanentlyDenied ||
                        status.isRestricted ||
                        status.isDenied) {
                      await Permission.camera.request();
                    }
                    var scanResult = await FlutterBarcodeScanner.scanBarcode(
                        "#ffffff",
                        "Hủy",
                        true,
                        ScanMode.DEFAULT);
                    if(scanResult=="-1"){
                      return;
                    }
                    listProduct.editingController.text = scanResult;
                    listProduct.filterSearchResults(scanResult);
                  }),
            ],
          ),
          // drawer: ExpansionDrawer(this.context),
          body: Container(
            child: Column(
              children: <Widget>[
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: TextField(
                    onSubmitted: (value) {
                      listProduct.filterSearchResults(value);
                    },
                    controller: listProduct.editingController,
                    decoration: InputDecoration(
                      labelText: "Tìm kiếm",
                      hintText: "Nhập tên hàng hoặc mã vạch, QR code",
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(25.0))),
                      suffixIcon: IconButton(
                        onPressed: () => listProduct.editingController.clear(),
                        icon: Icon(Icons.clear),
                      ),
                    ),
                  ),
                ),
                Container(
                  decoration: BoxDecoration(color: Colors.grey),
                  child: Padding(
                    padding: EdgeInsets.all(8.0),
                    child: SizedBox(
                      height: 40.0,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          FlatButton(
                            onPressed: () {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return sortListCriteria;
                                  });
                            },
                            child: Column(
                              children: [Icon(Icons.sort), Text("Sắp xếp")],
                            ),
                          ),
                          FlatButton(
                            onPressed: () {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return listCategory;
                                  });
                            },
                            child: Column(
                              children: [Icon(Icons.ballot), Text("Danh mục")],
                            ),
                          ),
/*                        FlatButton(
                            onPressed: (){},
                            child: Column(
                              children: [
                                Icon(Icons.ballot),
                                Text("Bảng giá")
                              ],
                            ),
                          ),*/
                        ],
                      ),
                    ),
                  ),
                ),
                listProduct
              ],
            ),
          ),
        ),
      ),
    );
  }
}

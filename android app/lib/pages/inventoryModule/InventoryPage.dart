import 'package:bkrm/pages/inventoryModule/category/addNewCategory.dart';
import 'package:bkrm/pages/inventoryModule/import/importGoodPage.dart';
import 'package:bkrm/pages/inventoryModule/item/itemDetailPage.dart';
import 'package:bkrm/pages/inventoryModule/item/addNewProductPage.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:bkrm/widget/sortListCriteria.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:page_transition/page_transition.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:bkrm/widget/listProducts.dart';
import 'package:bkrm/widget/listCategory.dart';

import 'package:bkrm/pages/Nav2App.dart';

class InventoryPage extends StatefulWidget {
  InventoryPage({Key? key, this.title}) : super(key: key);
  final String? title;
  _InventoryPageState? _state;


  @override
  _InventoryPageState createState() => _InventoryPageState();
}

class _InventoryPageState extends State<InventoryPage> {
  late SortListCriteriaProduct sortListCriteria;
  late ListProduct listProduct;
  late ListCategory listCategory;
  refresh() {
    print("refresh on state");
    setState(() {});
  }
  @override
  void initState() {
    super.initState();
    listProduct= ListProduct(
      hasSlider: false,
      onTapOnProduct: (context,rawDataItem){
        debugPrint("Excute on tap on Product");
        Navigator.push(context,PageTransition(child: ItemDetailPage(rawDataItem), type: pageTransitionType)).then((value){
          debugPrint(value.toString());
          if(value!=null){
            if(value){
              debugPrint("refresh list");
              listProduct.refreshList();
            }
          }
        });
      },
    );
    sortListCriteria=SortListCriteriaProduct(listProduct);
    listCategory=ListCategory(listProduct);
  }
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: (){
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: new Scaffold(resizeToAvoidBottomInset: false,
        appBar: new AppBar(
          title: new Text(widget.title!),
          actions: [
            PopupMenuButton(
                onSelected: (dynamic value) {
                  switch (value) {
                    case 0:
                      Navigator.push(context,
                          PageTransition(child: AddNewItemPage(), type: pageTransitionType)).then((value){
                        if(value!=null){
                          if(value){
                            listProduct.refreshList();
                          }
                        }
                      });
                      break;
                    case 1:
                      Navigator.push(context,
                          PageTransition(child: AddNewCategoryPage(),type: pageTransitionType));
                      break;
                  }
                },
                icon: Icon(Icons.add),
                itemBuilder: (BuildContext context) {
                  return <PopupMenuItem>[
                    PopupMenuItem(
                      child: Text("Th??m h??ng h??a"),
                      value: 0,
                    ),
                    PopupMenuItem(
                      value: 1,
                      child: Text("Th??m danh m???c"),
                    )
                  ];
                }),
            IconButton(icon: Icon(Icons.add_business), onPressed: (){
              Navigator.push(context,PageTransition(child: ImportGoodPage(),type: pageTransitionType));
            }),
            IconButton(
                icon: Icon(Icons.qr_code),
                onPressed: () async {
                  var status = await Permission.camera.status;
                  if (status.isPermanentlyDenied||status.isRestricted || status.isDenied) {
                    await Permission.camera.request();
                  }
                  var scanResult = await FlutterBarcodeScanner.scanBarcode(
                      "#ffffff",
                      "H???y",
                      true,
                      ScanMode.DEFAULT);
                  if(scanResult=="-1"){
                    return;
                  }
                  listProduct.editingController.text=scanResult;
                  listProduct.filterSearchResults(scanResult);
                }),
          ],
        ),
        drawer: ExpansionDrawer(this.context),
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
                      labelText: "T??m ki???m",
                      hintText: "Nh???p t??n h??ng ho???c m?? v???ch, QR code",
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(25.0))),
                    suffixIcon: IconButton(
                      onPressed: () => listProduct.editingController.clear(),
                      icon: Icon(Icons.clear),
                    ),),
                ),
              ),
              Container(
                decoration: BoxDecoration(color: Colors.grey),
                child: Padding(
                  padding: EdgeInsets.all(8.0),
                  child: SizedBox(
                    height: 40.0,
                    child: Stack(
                      children: [
                        Align(
                          alignment: Alignment.centerLeft,
                          child: FlatButton(
                            onPressed: (){
                              showDialog(context: context,builder: (context){
                                return sortListCriteria;
                              });
                            },
                            child: Column(
                              children: [
                                Icon(Icons.compare_arrows_sharp),
                                Text("S???p x???p")
                              ],
                            ),
                          ),
                        ),
                        Align(
                          alignment: Alignment.centerRight,
                          child: FlatButton(
                            onPressed: (){
                              showDialog(context: context,builder: (context){
                                return listCategory;
                              });
                            },
                            child: Column(
                              children: [Icon(Icons.ballot), Text("Danh m???c")],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              listProduct,
/*            Align(
                alignment: Alignment.bottomCenter,
                child: Container(
                  alignment: Alignment.center,
                  decoration: BoxDecoration(color: Colors.grey),
                  child: StreamBuilder(
                    stream: listProduct.getDataDone,
                    initialData: {
                      "sum_products": 0,
                      "sum_all_quantity_of_products": 0
                    },
                    builder: (context, snapshot) {
                      Map streamTotalAmount = snapshot.data as Map<String,dynamic>;
                      return Text("C?? t???ng c???ng " +
                          streamTotalAmount["sum_products"].toString() +
                          " m???t h??ng v???i s??? t???n kho l?? " +
                          streamTotalAmount["sum_all_quantity_of_products"]
                              .toString());
                    },
                  ),
                  height: 20,
                  width: MediaQuery.of(context).size.width,
                ),
              )*/
            ],
          ),
        ),
      ),
    );
  }
}

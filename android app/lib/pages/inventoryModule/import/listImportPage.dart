import 'package:bkrm/pages/inventoryModule/category/addNewCategory.dart';
import 'package:bkrm/pages/inventoryModule/item/addNewProductPage.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/sortListCriteria.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:page_transition/page_transition.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:bkrm/widget/listProducts.dart';
import 'package:bkrm/widget/listCategory.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListImportPage extends StatefulWidget {
  ListImportPage({Key? key, this.title}) : super(key: key);
  final String? title;
  _ListImportPageState? _state;


  @override
  _ListImportPageState createState() => _ListImportPageState();
}

class _ListImportPageState extends State<ListImportPage> {
  late SortListCriteriaProduct sortListCriteria;
  ListProduct listProduct = ListProduct(
    hasSlider: false,
    onTapOnProduct: (context,rawDataItem){
        BkrmService().importGood!.addToImport(rawDataItem);
        Scaffold.of(context).showSnackBar(SnackBar(
          content: Text("Đã thêm thành công 1 mặt hàng vào danh sách nhập hàng"),
          action: SnackBarAction(
            label: "Huỷ",
            onPressed: (){
              BkrmService().importGood!.deleteItem((rawDataItem as ItemInfo).priceId);
              final scaffold = Scaffold.of(context);
              scaffold.showSnackBar(SnackBar(
                content: Text("Đã huỷ thành công"),
                action: SnackBarAction(
                    label: "Ẩn",
                    onPressed: scaffold.hideCurrentSnackBar
                ),
              ));
            },
          ),
        ));
    },
  );
  late ListCategory listCategory;
  refresh() {
    print("refresh on state");
    setState(() {});
  }
  @override
  void initState() {
    super.initState();
    sortListCriteria=SortListCriteriaProduct(listProduct);
    listCategory=ListCategory(listProduct);
  }
  @override
  Widget build(BuildContext context) {
    return new Scaffold(
      appBar: new AppBar(
        title: new Text(widget.title!),
        actions: [
          PopupMenuButton(
              onSelected: (dynamic value) {
                switch (value) {
                  case 0:
                    Navigator.push(context,
                        PageTransition(child:AddNewItemPage(),type:pageTransitionType));
                    break;
                  case 1:
                    Navigator.push(context,
                        PageTransition(child: AddNewCategoryPage(), type: pageTransitionType));
                    break;
                }
              },
              icon: Icon(Icons.add),
              itemBuilder: (BuildContext context) {
                return <PopupMenuItem>[
                  PopupMenuItem(
                    child: Text("Thêm hàng hóa"),
                    value: 0,
                  ),
                  PopupMenuItem(
                    value: 1,
                    child: Text("Thêm danh mục"),
                  )
                ];
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
      body: Container(
        child: Column(
          children: <Widget>[
            Padding(
              padding: const EdgeInsets.all(8.0),
              child: TextField(
                onChanged: (value) {
                  listProduct.filterSearchResults(value);
                },
                controller: listProduct.editingController,
                decoration: InputDecoration(
                    labelText: "Tìm kiếm",
                    hintText: "Nhập tên hàng hoặc mã vạch, QR code",
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.all(Radius.circular(25.0)))),
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
                              Text("Sắp xếp")
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
                            children: [Icon(Icons.ballot), Text("Danh mục")],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            listProduct,
          ],
        ),
      ),
    );
  }
}

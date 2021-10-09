import 'package:bkrm/pages/inventoryModule/item/itemDetailPage.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/importGoodService.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:bkrm/services/cartService.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ImportItemCard extends StatelessWidget {
  ImportGoodItem? importItem;
  ApiService api = ApiService();
  BkrmService bkrmService = BkrmService();
  ImportItemCard(this.importItem){
    capitalController.text=importItem!.importedPrice!=null?NumberFormat().format(importItem!.importedPrice):"";
    capitalController.selection = TextSelection.fromPosition(TextPosition(offset: capitalController.text.length));
  }
  NumberFormat formatter = NumberFormat();
  TextEditingController capitalController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: (){
          Navigator.push(context, PageTransition(child: ItemDetailPage(this.importItem!.item), type: pageTransitionType));
        },
        child: Stack(children: [
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Expanded(
                  flex: 2,
                  child: Padding(
                      padding: EdgeInsets.only(right: 5),
                      child: importItem!.item.imageUrl == null
                          ? Image.asset("asset/productImage/no-image.jpg")
                          : CachedNetworkImage(imageUrl:
                              ServerConfig.projectUrl + importItem!.item.imageUrl!,        progressIndicatorBuilder: (context, url,downloadProgress) =>
                          SizedBox(height:20,width:20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,))),
                        errorWidget: (context, url, error) => Icon(Icons.error),)),
                ),
                Expanded(
                  flex: 5,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        importItem!.item.itemName!,
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                      // Text("Đơn vị:" + importItem.item.unitName.toString()),
                      SizedBox(
                        width: 90,
                        child: ButtonBar(
                          buttonPadding: EdgeInsets.zero,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            SizedBox(
                              width: 30,
                              child: IconButton(
                                  padding: EdgeInsets.all(2),
                                  iconSize: 12,
                                  icon: Icon(Icons.remove),
                                  onPressed: () {
                                    importItem!.amount = importItem!.amount - 1;
                                    bkrmService.importGood!.replaceItem(importItem);
                                  }),
                            ),
                            SizedBox(
                                width: 30,
                                child: RaisedButton(
                                    child: Text(importItem!.amount.toString()),
                                    onPressed: () {
                                      showDialog(
                                          context: context,
                                          builder: (BuildContext context) {
                                            return CustomDialog(importItem: importItem);
                                          });
                                    })),
                            SizedBox(
                              width: 30,
                              child: IconButton(
                                  padding: EdgeInsets.all(2),
                                  iconSize: 12,
                                  icon: Icon(Icons.add),
                                  onPressed: () {
                                    importItem!.amount = importItem!.amount + 1;
                                    bkrmService.importGood!.replaceItem(importItem);
                                  }),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  flex: 3,
                  child: Column(
                    children: [
                      Text("Giá nhập :"),
                      Row(
                        children: [
                          Expanded(
                            flex:3,
                            child: TextFormField(
                              controller: capitalController,
                                inputFormatters: [
                                  CustomerFormatter().currencyFormatter
                                ],
                            autovalidateMode: AutovalidateMode.onUserInteraction,
                            validator: (price){
                              if(price==""||price==null){
                                return " * Bắt buộc";
                              }
                              price=price.replaceAll(",",  "");
                              if(int.tryParse(price)!=null){
                                if(int.tryParse(price)!<0){
                                  return " * Phải là số nguyên dương";
                                }
                                return null;
                              }else{
                                return null;
                              }
                            },
                              onChanged:(value){
                                debugPrint(value.toString());
                                value=value.replaceAll(",", "");
                                if(int.tryParse(value)!=null){
                                  importItem!.importedPrice=int.tryParse(value)!;
                                }
                              }
                              ,
                            keyboardType: TextInputType.number,
                        ),
                          ),
                        Expanded(flex:1,child: Text(" VNĐ",style: TextStyle(fontSize: 12),))]
                      )
                    ],
                  )
                ),
              ],
            ),
          ),
          Positioned(
            top: -1,
            right: 0,
            child: SizedBox(
              width: 30,
              child: IconButton(
                  iconSize: 18,
                  icon: Icon(Icons.close),
                  onPressed: () {
                    bkrmService.importGood!.deleteItem(importItem!.item.priceId);
                  }),
            ),
          ),
        ]),
      ),
    );
  }
}

class CustomDialog extends StatefulWidget {
  CartItem? cartItem;
  ImportGoodItem? importItem;
  CustomDialog({CartItem? cartItem,ImportGoodItem? importItem}){
    if(cartItem!=null){
      this.cartItem=cartItem;
    }
    if(importItem!=null){
      this.importItem=importItem;
    }
  }
  @override
  _CustomDialogState createState() => _CustomDialogState();
}

class _CustomDialogState extends State<CustomDialog> {
  BkrmService bkrmService = BkrmService();
  TextEditingController controller = TextEditingController();
  bool _validate = true;
  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Nhập số lượng"),
      content: TextField(
        inputFormatters: [CustomerFormatter().numberFormatter],
        keyboardType: TextInputType.number,
        controller: controller,
        decoration: new InputDecoration(
            hintText: _validate
                ? "Nhập số lớn hơn 0"
                : "Số nhập vào không hợp lệ!",
            hintStyle: _validate
                ? TextStyle(color: Colors.grey)
                : TextStyle(color: Colors.red)),
      ),
      actions: [
        FlatButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: Text("Hủy")),
        FlatButton(
            onPressed: () {
              if (int.tryParse(controller.value.text) != null) {
                int amount = int.tryParse(controller.value.text)!;
                if (amount > 0) {
                  if(widget.cartItem!=null){
                    widget.cartItem!.amount = amount;
                    bkrmService.cart!.modifyCartItem(widget.cartItem);
                    Navigator.pop(context);
                    return;
                  }
                  if(widget.importItem!=null){
                    widget.importItem!.amount = amount;
                    bkrmService.importGood!.replaceItem(widget.importItem);
                    Navigator.pop(context);
                    return;
                  }
                }
              }
              controller.text = "";
              _validate = false;
              setState(() {});
            },
            child: Text("Xác nhận")),
      ],
    );
  }
}

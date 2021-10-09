import 'package:bkrm/main.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:bkrm/services/cartService.dart';
import 'package:intl/intl.dart';

class CartItemCard extends StatelessWidget {
  final CartItem? cartItem;
  final ApiService api = ApiService();
  final BkrmService bkrmService = BkrmService();
  CartItemCard(this.cartItem);
  final NumberFormat formatter = NumberFormat();
  @override
  Widget build(BuildContext context) {
    return InkWell(
      onLongPress: BkrmService().currentUser!.roles.contains("purchasing")?(){
        showDialog(context: context, builder: (context){
          TextEditingController controller = TextEditingController();
          controller.text=NumberFormat().format(cartItem!.item.sellPrice);
          bool numberValid = false;
          return AlertDialog(
            title: Text("Thay đổi giá"),
            content: TextFormField(
              keyboardType: TextInputType.number,
              inputFormatters: [CustomerFormatter().currencyFormatter],
              controller: controller,
              autovalidateMode: AutovalidateMode.always,
              validator: (value){
                if(value==""||value==null){
                  numberValid=false;
                  return " * Bắt buộc";
                }
                if((int.tryParse(value)??0)<0){
                  numberValid=false;
                  return "Nhập số > 0";
                }
                numberValid=true;
                return null;
              },
            ),
            actions: [TextButton(onPressed: (){Navigator.pop(context);}, child: Text("Hủy")),
            TextButton(onPressed: ()async{
              if(numberValid){
                int? sellPrice = int.tryParse(controller.value.text.replaceAll(",", ""));
                showDialog(context: context, builder: (context){
                  return AlertDialog(
                    content:Container(
                      height: 50,
                      child: Center(
                        child: CircularProgressIndicator(),
                      ),
                    )
                  );
                });
                MsgInfoCode? returnStatus = await BkrmService().editProduct(categoryId: cartItem!.item.categoryId, itemId: cartItem!.item.itemId, itemName: cartItem!.item.itemName!,
                    barCode: cartItem!.item.barCode, quantity: cartItem!.item.quantity, sellValue: sellPrice??cartItem!.item.sellPrice,
                    deleted: false, pointRatio: cartItem!.item.pointRatio, imageFile: null, purchasePrice: cartItem!.item.purchasePrice);
                Navigator.pop(context);
                if(returnStatus==MsgInfoCode.actionSuccess){
                  cartItem!.item.sellPrice=int.tryParse(controller.value.text)??cartItem!.item.sellPrice;
                  BkrmService().requestCart();
                  BkrmService().cart!.checkCartValid();
                  BkrmService().cart!.calculateAllValueInCart();
                  showDialog(context: context, builder: (context){
                    return AlertDialog(
                        title: Text("Chỉnh sửa thành công."),
                      actions: [TextButton(onPressed: (){Navigator.pop(context);Navigator.pop(context);}, child: Text("Hoàn thành"))],
                    );
                  });
                }else{
                  showDialog(context: context, builder: (context){
                    return AlertDialog(
                      title: Text("Chỉnh sửa thất bại."),
                      actions: [TextButton(onPressed: (){Navigator.pop(context);}, child: Text("Xác nhận"))],
                    );
                  });
                }
              }
            }, child: Text("Xác nhận"))],
          );
        });
      }:null,
      child: Card(
          child: InkWell(
        child: Stack(children: [
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Expanded(
                  flex: 2,
                  child: Padding(
                      padding: EdgeInsets.only(right: 5),
                      child: cartItem!.item.imageUrl == null
                          ? Image.asset("asset/productImage/no-image.jpg")
                          : CachedNetworkImage(imageUrl:
                              ServerConfig.projectUrl + cartItem!.item.imageUrl!,        progressIndicatorBuilder: (context, url,downloadProgress) =>
                          SizedBox(height:20,width:20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,))),
                        errorWidget: (context, url, error) => Icon(Icons.error),)),
                ),
                Expanded(
                  flex: 5,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        cartItem!.item.itemName!,
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                      // Text("Đơn vị:" + cartItem.item.unitName.toString()),
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
                                    cartItem!.amount = cartItem!.amount - 1;
                                    bkrmService.cart!.modifyCartItem(cartItem);
                                  }),
                            ),
                            SizedBox(
                                width: 30,
                                child: RaisedButton(
                                    child: Text(cartItem!.amount.toString()),
                                    onPressed: () {
                                      showDialog(
                                          context: context,
                                          builder: (BuildContext context) {
                                            return CustomDialog(cartItem);
                                          });
                                    })),
                            SizedBox(
                              width: 30,
                              child: IconButton(
                                  padding: EdgeInsets.all(2),
                                  iconSize: 12,
                                  icon: Icon(Icons.add),
                                  onPressed: () {
                                    cartItem!.amount = cartItem!.amount + 1;
                                    bkrmService.cart!.modifyCartItem(cartItem);
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
                    children: cartItem!.discountPrice == cartItem!.item.sellPrice
                        ? [
                            Text(formatter.format(cartItem!.discountPrice) +
                                " VNĐ")
                          ]
                        : [
                            Text(formatter.format(cartItem!.discountPrice) +
                                " VNĐ"),
                            Text(
                              formatter.format(cartItem!.item.sellPrice) + " VNĐ",
                              style: TextStyle(
                                  decoration: TextDecoration.lineThrough),
                            )
                          ],
                  ),
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
                    bkrmService.cart!.removeCartItem(cartItem);
                  }),
            ),
          ),
          Positioned(
              bottom: 0,
              right: 0,
              child: cartItem!.valid
                  ? Container()
                  : Icon(
                      Icons.error,
                      color: Colors.red,
                    )),
        ]),
      )),
    );
  }
}

class CustomDialog extends StatefulWidget {
  CartItem? cartItem;
  CustomDialog(this.cartItem);
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
                  widget.cartItem!.amount = amount;
                  bkrmService.cart!.modifyCartItem(widget.cartItem);
                  Navigator.pop(context);
                  return;
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

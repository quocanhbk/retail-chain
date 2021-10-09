import 'package:bkrm/services/printer/qrCodePrinter.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

class DetailBranch extends StatefulWidget {
  @override
  _DetailBranchState createState() => _DetailBranchState();
}

class _DetailBranchState extends State<DetailBranch> {
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();

  bool nameValid = true;
  bool addressValid = true;

  bool isEditting = false;

  @override
  void initState() {
    setUpBranchInfo();
    super.initState();
  }

  setUpBranchInfo() {
    nameController.text = BkrmService().currentUser!.branchName;
    addressController.text = BkrmService().currentUser!.branchAddress;
  }

  clearPage() {
    nameController.clear();
    addressController.clear();
    nameValid = true;
    addressValid = true;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        appBar: AppBar(
          title: Text(" Thông tin cửa hàng"),
        ),
        drawer: ExpansionDrawer(context),
        body: SingleChildScrollView(
          child: Container(
            child: Padding(
              padding: const EdgeInsets.all(8.0),
              child: Column(
                children: [
                  SizedBox(
                    height: 50,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Tên cửa hàng : ",
                            style: TextStyle(
                                fontSize: 15, fontWeight: FontWeight.w400),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: isEditting,
                          controller: nameController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (name) {
                            if (name == null || name == "") {
                              nameValid = false;
                              return " *Bắt buộc";
                            }
                            nameValid = true;
                            return null;
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue)),
                              hintText: "Nhập tên cửa hàng",
                              hintStyle: TextStyle(
                                  color: Colors.grey,
                                  fontWeight: FontWeight.w300)),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Địa chỉ : ",
                            style: TextStyle(
                                fontSize: 15, fontWeight: FontWeight.w400),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: isEditting,
                          controller: addressController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (address) {
                            if (address == null || address == "") {
                              addressValid = false;
                              return " *Bắt buộc";
                            }
                            addressValid = true;
                            return null;
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue)),
                              hintText: "Nhập địa chỉ cửa hàng",
                              hintStyle: TextStyle(
                                  color: Colors.grey,
                                  fontWeight: FontWeight.w300)),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Center(
                    child: Text(
                      "Trang thông tin sản phẩm cửa hàng: ",
                      style:
                          TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
                    ),
                  ),
                  QrImage(
                    data: "https://149.28.148.73/bkrm/public/guest/" +
                        BkrmService().currentUser!.branchId.toString() +
                        "/item-list",
                    size: 200,
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(children: [
                    Expanded(
                      flex: 1,
                      child: Container(
                        alignment: Alignment.centerRight,
                        child: InkWell(
                            onTap: () async {
                              await canLaunch(
                                      "https://149.28.148.73/bkrm/public/guest/" +
                                          BkrmService()
                                              .currentUser!
                                              .branchId
                                              .toString() +
                                          "/item-list")
                                  ? await launch(
                                      "https://149.28.148.73/bkrm/public/guest/" +
                                          BkrmService()
                                              .currentUser!
                                              .branchId
                                              .toString() +
                                          "/item-list")
                                  : showDialog(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Text(
                                              "Không mở được trình duyệt để truy cập."),
                                          actions: [
                                            TextButton(
                                                onPressed: () {
                                                  Navigator.pop(context);
                                                },
                                                child: Text("Đóng"))
                                          ],
                                        );
                                      });
                            },
                            child: Text(
                              "Truy cập",
                              style: TextStyle(
                                  decoration: TextDecoration.underline,
                                  color: Colors.blue),
                            )),
                      ),
                    ),
                        Expanded(
                          flex: 1,
                          child: Container(
                            alignment: Alignment.centerLeft,
                            child: IconButton(onPressed: (){
                              showDialog(context: context, builder: (context){
                                return QrCodePrinter("https://149.28.148.73/bkrm/public/guest/" +
                                    BkrmService()
                                        .currentUser!
                                        .branchId
                                        .toString() +
                                    "/item-list");
                              });
                            }, icon: Icon(Icons.print)),
                          ),
                        )
                  ]),
                  SizedBox(
                    height: 20,
                  ),
                  isEditting
                      ? Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            TextButton(
                                onPressed: () {
                                  clearPage();
                                  setUpBranchInfo();
                                  isEditting = false;
                                  setState(() {});
                                },
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: Colors.blue,
                                    borderRadius: BorderRadius.circular(5.0),
                                  ),
                                  padding: EdgeInsets.all(10.0),
                                  child: Text("Hủy",
                                      style: TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 20)),
                                )),
                            TextButton(
                                onPressed: () async {
                                  if (nameValid && addressValid) {
                                    showDialog(
                                        context: context,
                                        builder: (context) {
                                          return AlertDialog(
                                            content: Container(
                                              height: 50,
                                              child: Center(
                                                child:
                                                    CircularProgressIndicator(),
                                              ),
                                            ),
                                          );
                                        });
                                    MsgInfoCode? returnStatus =
                                        await BkrmService().editBranch(
                                            name: nameController.value.text,
                                            address:
                                                addressController.value.text);
                                    Navigator.pop(context);
                                    if (returnStatus == null ||
                                        returnStatus ==
                                            MsgInfoCode.actionFail) {
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text("Đã có lổi xảy ra!"),
                                              actions: [
                                                TextButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                    },
                                                    child: Text("Đóng"))
                                              ],
                                            );
                                          });
                                    } else {
                                      BkrmService().currentUser!.branchName =
                                          nameController.value.text;
                                      BkrmService().currentUser!.branchAddress =
                                          nameController.value.text;
                                      isEditting = false;
                                      setState(() {});
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text(
                                                  "Đổi thông tin cửa hàng thành công."),
                                              actions: [
                                                TextButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                    },
                                                    child: Text("Đóng"))
                                              ],
                                            );
                                          });
                                    }
                                  }
                                },
                                child: Container(
                                    decoration: BoxDecoration(
                                      color: Colors.blue,
                                      borderRadius: BorderRadius.circular(5.0),
                                    ),
                                    padding: EdgeInsets.all(10.0),
                                    child: Center(
                                        child: Text(
                                      "Xác nhận",
                                      style: TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 20),
                                    )))),
                          ],
                        )
                      : Center(
                          child: TextButton(
                            onPressed: () {
                              if (!isEditting) {
                                isEditting = true;
                                setState(() {});
                              }
                            },
                            child: Container(
                              decoration: BoxDecoration(
                                color: Colors.blue,
                                borderRadius: BorderRadius.circular(5.0),
                              ),
                              padding: EdgeInsets.all(10.0),
                              child: Text("Chỉnh sửa",
                                  style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 20)),
                            ),
                          ),
                        )
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

from fpdf import FPDF
import os

class PDF(FPDF):
    def header(self):
        self.set_font("Tahoma", 'B', 16)
        self.cell(0, 10, "รายงานสรุปรายละเอียดโปรเจกต์เว็บสั่งพิมพ์งาน (OOAD & OOP)", align='C')
        self.ln(15)

    def footer(self):
        self.set_y(-15)
        self.set_font("Tahoma", '', 8)
        self.cell(0, 10, f'หน้า {self.page_no()}', align='C')

pdf = PDF()
pdf.add_font("Tahoma", "", "C:\\Windows\\Fonts\\tahoma.ttf")
pdf.add_font("Tahoma", "B", "C:\\Windows\\Fonts\\tahomabd.ttf")

pdf.add_page()
pdf.set_font("Tahoma", '', 12)

content = """
1. ภาพรวมของโปรเจกต์
โปรเจกต์นี้เป็นการพัฒนาระบบสั่งพิมพ์งาน (Printing Service) ขนาดเล็กด้วยภาษา PHP 
โดยอ้างอิงจากแผนภาพคลาส (Class Diagram) ที่กำหนดให้ และประยุกต์ใช้หลักการ 
Object-Oriented Analysis and Design (OOAD) และ Object-Oriented Programming (OOP)
เพื่อให้โค้ดมีโครงสร้างที่เป็นระเบียบ ยืดหยุ่น ต่อการบำรุงรักษาและขยายต่อในอนาคต

2. หลักการ OOP และ OOAD ที่นำมาใช้
2.1 Abstraction (นามธรรม)
การดึงเฉพาะคุณลักษณะและพฤติกรรมที่จำเป็นมาสร้างเป็นโครงสร้างหลัก เช่น การสร้าง 
ServiceInterface เพื่อกำหนดว่าบริการทุกประเภทต้องมีฟังก์ชันคำนวณราคา 
(calculatePrice) และการดึงชื่อบริการ (getName)

2.2 Encapsulation (การห่อหุ้ม)
การซ่อนข้อมูล (Data Hiding) และตรรกะการทำงานไว้ภายในคลาส ตัวอย่างเช่น
คลาส Paper มีฟังก์ชัน getPrice() ที่ห่อหุ้มตรรกะการคำนวณราคากระดาษเอาไว้
ภายนอกไม่จำเป็นต้องรู้ว่าคำนวณอย่างไร เพียงแค่เรียกใช้งานฟังก์ชันก็จะได้ราคาที่ถูกต้อง

2.3 Polymorphism (พ้องรูป)
ความสามารถในการใช้โครงสร้างเดียวกันแต่ให้ผลลัพธ์การทำงานที่ต่างกัน 
ตัวอย่างเช่น PrintService และ FinishingService ต่างก็ implements ServiceInterface 
เมื่อระบบเรียกใช้ calculatePrice() ของบริการใดๆ ผลลัพธ์ที่ได้จะขึ้นอยู่กับว่า
เป็นบริการประเภทใดโดยที่ส่วนที่เรียกใช้งานไม่ต้องแยกเงื่อนไข

2.4 Composition (องค์ประกอบ)
การที่คลาสหนึ่งถูกสร้างขึ้นมาจากคลาสอื่น ตัวอย่างในระบบนี้คือคลาส Order
ที่มีความสัมพันธ์แบบ Composition กับ OrderItem (มีสัญลักษณ์ข้าวหลามตัดทึบใน UML)
หมายความว่า 1 คำสั่งซื้อ (Order) สามารถประกอบไปด้วยหลายรายการย่อย (OrderItem)

3. คำอธิบายรายละเอียดของแต่ละส่วน (Class Details)

3.1 PaperSize (Enumeration)
- หน้าที่: กำหนดค่าคงที่สำหรับขนาดกระดาษ (A4, A3) ป้องกันการป้อนข้อมูลผิดพลาด
- ความสัมพันธ์: นำไปใช้ในคลาส Paper

3.2 Paper (Class)
- หน้าที่: เก็บข้อมูลกระดาษ ได้แก่ ขนาด (PaperSize) และชนิด (กระดาษแข็งหรือไม่)
- เมธอดสำคัญ: getPrice() ใช้คำนวณราคากระดาษตามขนาดและชนิด

3.3 ServiceInterface (Interface)
- หน้าที่: กำหนดมาตรฐานสำหรับบริการ (Service)
- เมธอดที่กำหนด: calculatePrice(Paper, pages, quantity) และ getName()

3.4 PrintService (Class)
- หน้าที่: บริการงานพิมพ์ (สืบทอดแบบ implements จาก ServiceInterface)
- คุณสมบัติเพิ่มเติม: เก็บข้อมูลประเภทการพิมพ์ (type) และรูปแบบ (isColor: สี/ขาวดำ)
- เมธอดสำคัญ: คำนวณราคาโดยบวกรวมราคากระดาษกับราคาฐานของการพิมพ์สีหรือขาวดำ

3.5 FinishingService (Class)
- หน้าที่: บริการเข้าเล่ม (สืบทอดแบบ implements จาก ServiceInterface)
- คุณสมบัติเพิ่มเติม: เก็บข้อมูลราคาต่อหน่วย (unitPrice)
- เมธอดสำคัญ: คำนวณราคาจากการนำจำนวน (quantity) คูณกับราคาต่อหน่วย

3.6 OrderItem (Class)
- หน้าที่: เป็นรายการสินค้า 1 รายการ
- ความสัมพันธ์: ประกอบด้วย ServiceInterface, Paper, จำนวนหน้า (pages) และจำนวนชุด (quantity)
- เมธอดสำคัญ: getSubtotal() ส่งต่อหน้าที่การคำนวณราคาให้กับตัว Service ที่เก็บไว้

3.7 Order (Class)
- หน้าที่: จัดการคำสั่งซื้อและข้อมูลลูกค้า (ชื่อลูกค้า, เบอร์โทรศัพท์)
- ความสัมพันธ์: เก็บ OrderItem หลายๆ ชิ้นในรูปแบบ Array (Composition)
- เมธอดสำคัญ: addItem() เพิ่มรายการ, getTotal() วนลูปคำนวณราคารวมทั้งหมดของคำสั่งซื้อ

4. หน้าจอการใช้งาน (Frontend)
ระบบมีไฟล์ index.php และ style.css สำหรับแสดงผลหน้าจอแบบฟอร์มการสั่งพิมพ์งาน 
รองรับการกรอกข้อมูลครบถ้วน และแสดงใบเสร็จรับเงินเมื่อผู้ใช้กดสั่งงาน
"""

for line in content.split('\n'):
    pdf.multi_cell(0, 8, line)

pdf.output("Project_Summary.pdf")

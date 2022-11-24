-------------------- spu_sucursal ---------------------------------
CREATE PROCEDURE [dbo].[spu_sucursal](@ve_operacion varchar(20),@ve_cod_sucursal numeric ,@ve_nom_sucursal varchar(100)=NULL, @ve_cod_empresa numeric=NULL, @ve_direccion varchar(100)=NULL, @ve_cod_comuna numeric=NULL, @ve_cod_ciudad numeric=NULL, @ve_cod_pais numeric=NULL, @ve_direccion_factura varchar(1)=NULL, @ve_direccion_despacho varchar(1)=NULL, @ve_telefono varchar(100)=NULL, @ve_fax varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into sucursal (nom_sucursal, cod_empresa, direccion, cod_comuna, cod_ciudad, cod_pais, direccion_factura, direccion_despacho, telefono, fax)
			values (@ve_nom_sucursal, @ve_cod_empresa, @ve_direccion, @ve_cod_comuna, @ve_cod_ciudad, @ve_cod_pais, @ve_direccion_factura, @ve_direccion_despacho, @ve_telefono, @ve_fax)
		end 
	else if (@ve_operacion='UPDATE')
		begin
			update sucursal
			set nom_sucursal = @ve_nom_sucursal,
				cod_empresa = @ve_cod_empresa, 
				direccion = @ve_direccion, 
				cod_comuna = @ve_cod_comuna, 
				cod_ciudad =@ve_cod_ciudad, 
				cod_pais = @ve_cod_pais, 
				direccion_factura = @ve_direccion_factura, 
				direccion_despacho = @ve_direccion_despacho, 
				telefono = @ve_telefono, 
				fax = @ve_fax
			where cod_sucursal = @ve_cod_sucursal	
		end 
	else if (@ve_operacion='DELETE') 
		begin
			delete persona	
			where cod_sucursal = @ve_cod_sucursal
		
			delete sucursal 
			where cod_sucursal = @ve_cod_sucursal
		end	
END
go
-------------------- spu_cuenta_contable ---------------------------------
CREATE PROCEDURE [dbo].[spu_cuenta_contable](@ve_operacion varchar(20), @ve_cod_cuenta_contable numeric, @ve_nom_cuenta_contable varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into cuenta_contable (cod_cuenta_contable, nom_cuenta_contable)
		values (@ve_cod_cuenta_contable, @ve_nom_cuenta_contable)
	end 
	if (@ve_operacion='UPDATE') begin
		update cuenta_contable 
		set nom_cuenta_contable = @ve_nom_cuenta_contable
	    where cod_cuenta_contable = @ve_cod_cuenta_contable
	end
	else if (@ve_operacion='DELETE') begin
		delete cuenta_contable 
    	where cod_cuenta_contable = @ve_cod_cuenta_contable
	end
	
END
go
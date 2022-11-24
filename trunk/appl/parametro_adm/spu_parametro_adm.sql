-------------------- spu_parametro_adm ---------------------------------
CREATE PROCEDURE [dbo].[spu_parametro_adm](
					@ve_iva					varchar(100), 		
					@ve_retencion_bh		varchar(100), 	
					@ve_dolar_com			varchar(100),	
					@ve_nom_empresa			varchar(100),		
					@ve_giro_empresa		varchar(100),	
					@ve_rut_empresa			varchar(100),		
					@ve_validez_ofer_cot	varchar(100),	
					@ve_entrega_cot			varchar(100),		
					@ve_garantia_cot		varchar(100),	
					@ve_direccion_empresa	varchar(100),	
					@ve_fono_empresa		varchar(100),	
					@ve_fax_empresa			varchar(100),		
					@ve_mail_empresa		varchar(100),	
					@ve_ciudad_empresa		varchar(100),	
					@ve_pais_empresa		varchar(100),	
					@ve_gerente_venta		varchar(100),	
					@ve_f_precio_int		varchar(100),	
					@ve_f_precio_pub		varchar(100),	
					@ve_f_precio_int_bajo	varchar(100),	
					@ve_f_precio_int_alto	varchar(100),	
					@ve_f_precio_pub_bajo	varchar(100),	
					@ve_f_precio_pub_alto	varchar(100),			
					@ve_aporte_aa			varchar(100),		
					@ve_fecha_aa			varchar(100),		
					@ve_visible_porc_aa		varchar(100),
					@ve_aporte_gf			varchar(100),		
					@ve_fecha_gf			varchar(100),		
					@ve_visible_porc_gf		varchar(100),
					@ve_aporte_gv			varchar(100),		
					@ve_fecha_gv			varchar(100),		
					@ve_visible_porc_gv		varchar(100),
					@ve_web_empresa			varchar(100),						
					@ve_porc_max_dscto		varchar(100),
					@ACCESO_LIBRE_A_COT		varchar(100),
					@ACCESO_LIBRE_A_NV		varchar(100),
					@ve_aporte_adm			varchar(100),		
					@ve_fecha_adm			varchar(100),		
					@ve_visible_porc_adm	varchar(100))
AS
BEGIN

update parametro set valor =  @ve_iva 				where cod_parametro = 1;
update parametro set valor =  @ve_retencion_bh 		where cod_parametro = 2;  	
update parametro set valor =  @ve_dolar_com			where cod_parametro = 5;
update parametro set valor =  @ve_nom_empresa		where cod_parametro = 6;
update parametro set valor =  @ve_validez_ofer_cot	where cod_parametro = 7;
update parametro set valor =  @ve_entrega_cot		where cod_parametro = 8;
update parametro set valor =  @ve_garantia_cot		where cod_parametro = 9;
update parametro set valor =  @ve_direccion_empresa	where cod_parametro = 10;
update parametro set valor =  @ve_fono_empresa		where cod_parametro = 11;
update parametro set valor =  @ve_fax_empresa		where cod_parametro = 12;
update parametro set valor =  @ve_mail_empresa		where cod_parametro = 13;
update parametro set valor =  @ve_ciudad_empresa	where cod_parametro = 14;
update parametro set valor =  @ve_pais_empresa		where cod_parametro = 15;
update parametro set valor =  @ve_gerente_venta		where cod_parametro = 16;
update parametro set valor =  @ve_rut_empresa		where cod_parametro = 20;
update parametro set valor =  @ve_giro_empresa		where cod_parametro = 21;
update parametro set valor =  @ve_f_precio_int		where cod_parametro = 22;
update parametro set valor =  @ve_f_precio_pub		where cod_parametro = 23;
update parametro set valor =  @ve_web_empresa		where cod_parametro = 25;
update parametro set valor =  @ve_porc_max_dscto	where cod_parametro = 26;
update parametro set valor =  @ve_f_precio_int_bajo	where cod_parametro = 34;
update parametro set valor =  @ve_f_precio_int_alto	where cod_parametro = 35;
update parametro set valor =  @ve_f_precio_pub_bajo	where cod_parametro = 36;
update parametro set valor =  @ve_f_precio_pub_alto	where cod_parametro = 37;
update parametro set valor =  @ACCESO_LIBRE_A_COT	where cod_parametro = 38;
update parametro set valor =  @ACCESO_LIBRE_A_NV	where cod_parametro = 39;


if (@ve_visible_porc_aa = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_aa, dbo.to_date(@ve_fecha_aa), 'AA');
	end

if (@ve_visible_porc_gf = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_gf, dbo.to_date(@ve_fecha_gf), 'GF');
	end
if (@ve_visible_porc_gv = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_gv, dbo.to_date(@ve_fecha_gv), 'GV');
	end
if (@ve_visible_porc_adm = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_adm, dbo.to_date(@ve_fecha_adm), 'ADM');
	end

END
go
